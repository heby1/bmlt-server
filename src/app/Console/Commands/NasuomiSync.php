<?php

namespace App\Console\Commands;

use App\Interfaces\FormatRepositoryInterface;
use App\Interfaces\MeetingRepositoryInterface;
use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\Format;
use App\Models\Meeting;
use App\Models\ServiceBody;
use App\Models\User;
use Carbon\Carbon;
use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class NasuomiSync extends Command
{
    protected $signature = 'nasuomi:sync
        {--dry-run : Fetch and report the proposed changes without changing the database}
        {--initialize-only : Configure the initial administrator, Suomen alue and native formats without fetching meetings}
        {--source-file= : Read a trusted complete JSON snapshot instead of REST (for tests/offline recovery)}';

    protected $description = 'Synchronize Finnish NA meetings from WordPress into the local BMLT database';

    private const AREA_NAME = 'Suomen alue';
    private const DEFAULT_PASSWORD = 'change-this-password-first-thing';
    private const RETENTION_SECONDS = 30 * 86400;
    private const WEEKDAYS = ['Sunnuntai' => 0, 'Maanantai' => 1, 'Tiistai' => 2, 'Keskiviikko' => 3, 'Torstai' => 4, 'Perjantai' => 5, 'Lauantai' => 6];
    private const STOCK_FORMATS = [
        'A&P' => ['St', 'Tr'],
        'Askel' => ['St'],
        'Perinnekokous' => ['Tr'],
        'Suljettu kirjallisuuskokous' => ['C', 'BK'],
        'Teema' => ['To'],
        'Meditaatio' => ['ME'],
        'Esteetön pääsy' => ['WC'],
        'Avoin' => ['O'],
    ];
    private const LANGUAGES = ['suomi' => 'FIN', 'englanti' => 'ENG', 'Farsinkielinen' => 'PER', 'ruotsi' => 'SWE', 'venäjä' => 'RUS'];
    private const CUSTOM_FORMATS = [
        'Avoin kuun ensimmäinen' => ['OFIRST', 'O'],
        'Avoin kuun viimeinen' => ['OLAST', 'O'],
        'Avoin vuosipäivinä' => ['OANNIV', 'O'],
        'Kuukauden toinen viikko avoin' => ['OSECOND', 'O'],
        'Tarvittaessa avoin' => ['OREQUEST', 'O'],
        'Askeltyökokous' => ['STEPWORK', 'FC1'],
        'Ei pääsyä pyörätuolilla' => ['NOWHEEL', 'FC2'],
        'Esteellinen' => ['OBSTRUCT', 'FC2'],
        'Tila ei ole esteetön' => ['NOACCESS', 'FC2'],
        'Laitos' => ['INST', 'FC3'],
        'Ryhmässä kerran kuussa alustaja, joka jakaa kokemustaan n.15min' => ['MONSPKR', 'FC1'],
        'ruotsi' => ['SWE', 'LANG'],
        'venäjä' => ['RUS', 'LANG'],
        'Tauolla' => ['PAUSED', 'FC3'],
    ];

    private array $flags = [];
    private array $coordinateCache = [];
    private array $overrides = [];
    private array $nativeFormats = [];
    private string $stateDirectory;

    public function handle(
        MeetingRepositoryInterface $meetings,
        ServiceBodyRepositoryInterface $serviceBodies,
        FormatRepositoryInterface $formats,
        UserRepositoryInterface $users,
    ): int {
        $locked = false;
        $runId = Carbon::now('UTC')->format('Ymd-His-u');
        $report = ['run' => $runId, 'source_url' => config('nasuomi.source_url'), 'dry_run' => (bool) $this->option('dry-run')];
        try {
            if (file_config('aggregator_mode_enabled')) {
                throw new RuntimeException('nasuomi:sync requires an ordinary BMLT server, with aggregator mode disabled.');
            }
            if ($this->option('initialize-only') && ($this->option('dry-run') || $this->option('source-file'))) {
                throw new RuntimeException('--initialize-only cannot be combined with --dry-run or --source-file.');
            }
            $this->flags = [];
            $this->nativeFormats = [];
            $this->prepareStateDirectory();
            $connection = DB::connection();
            $lockName = 'bmlt-nasuomi-' . substr(hash('sha256', $connection->getDatabaseName() . '|' . $connection->getTablePrefix()), 0, 32);
            $locked = (int) DB::selectOne('SELECT GET_LOCK(?, 0) AS acquired', [$lockName])->acquired === 1;
            if (!$locked) {
                throw new RuntimeException('Another Finnish synchronization is running.');
            }
            if ($this->option('initialize-only')) {
                DB::transaction(function () use ($users, $serviceBodies, $formats) {
                    $admin = $this->administrator($users, true);
                    request()->setUserResolver(fn () => $admin);
                    $area = $this->serviceBody($serviceBodies, $admin, false);
                    $this->prepareNativeFormats();
                    $this->formatIds($formats, false);
                    $this->info('Initialized Suomen alue (service body ' . $area->id_bigint . '). Existing custom passwords were preserved.');
                });
                $report['status'] = 'initialized';
            } else {
                // A complete, consistent source response is required before any database mutation.
                $source = $this->fetchSource();
                $this->writeJson('snapshots/' . $runId . '-source.json', $source);
                $report['source_count'] = count($source);
                $this->line('Fetched ' . count($source) . ' meetings; resolving locations using map links and the coordinate cache.');
                $this->loadCoordinates();
                $this->prepareNativeFormats();
                $normalized = [];
                foreach ($source as $row) {
                    try {
                        $normalized[$row['id']] = $this->normalize($row);
                    } catch (RuntimeException $e) {
                        $this->flag($row, 'skipped', 'invalid_source', $e->getMessage());
                    }
                }
                $this->writeJson('coordinates.json', $this->coordinateCache);
                $dryRun = (bool) $this->option('dry-run');
                $apply = function () use ($users, $serviceBodies, $formats, $meetings, $normalized, $dryRun) {
                    $admin = $dryRun ? null : $this->administrator($users, false);
                    if ($admin) {
                        request()->setUserResolver(fn () => $admin);
                    }
                    $area = $this->serviceBody($serviceBodies, $admin, $dryRun);
                    $formatIds = $this->formatIds($formats, $dryRun);
                    return $this->synchronize($meetings, $area, $normalized, $formatIds, $dryRun);
                };
                $report += $dryRun ? $apply() : DB::transaction($apply);
                $report['skipped'] = count($source) - count($normalized);
                $report['status'] = $dryRun ? 'preview' : ($report['skipped'] ? 'incomplete' : 'synchronized');
                $this->info(sprintf(
                    '%s: %d source, %d imported/planned, %d skipped; %d created, %d updated, %d deleted, %d unchanged.',
                    $dryRun ? 'Dry run' : 'Sync',
                    count($source),
                    count($normalized),
                    $report['skipped'],
                    $report['created'],
                    $report['updated'],
                    $report['deleted'],
                    $report['unchanged']
                ));
            }
            $report['flags'] = $this->flags;
            $this->writeJson('reports/' . $runId . '-report.json', $report);
            $this->pruneState();
            $this->line('Report: ' . $this->stateDirectory . '/reports/' . $runId . '-report.json');
            return !empty($report['skipped']) ? 2 : self::SUCCESS;
        } catch (Throwable $e) {
            $report['status'] = 'failed';
            $report['error'] = $e->getMessage();
            $report['flags'] = $this->flags;
            if (isset($this->stateDirectory) && is_dir($this->stateDirectory . '/reports')) {
                try {
                    $this->writeJson('reports/' . $runId . '-report.json', $report);
                } catch (Throwable) {
                    // Keep the original failure if writing its diagnostic also fails.
                }
            }
            $this->error($e->getMessage());
            return self::FAILURE;
        } finally {
            if ($locked) {
                DB::select('SELECT RELEASE_LOCK(?)', [$lockName]);
            }
        }
    }

    private function fetchSource(): array
    {
        if ($path = $this->option('source-file')) {
            $source = json_decode($this->readFile($path), true, 512, JSON_THROW_ON_ERROR);
        } else {
            $source = [];
            $total = null;
            $pages = null;
            for ($page = 1; $pages === null || $page <= $pages; $page++) {
                $response = Http::acceptJson()->withHeaders(['User-Agent' => 'BMLT-Finnish-Meeting-Sync/1.0'])
                    ->connectTimeout(10)->timeout(40)->retry(2, 500, throw: false)
                    ->get(config('nasuomi.source_url'), ['per_page' => 100, 'page' => $page, 'orderby' => 'id', 'order' => 'asc']);
                if (!$response->successful()) {
                    throw new RuntimeException('WordPress fetch failed on page ' . $page . ' (HTTP ' . $response->status() . '). Database unchanged.');
                }
                $headerTotal = $response->header('X-WP-Total');
                $headerPages = $response->header('X-WP-TotalPages');
                if (!ctype_digit((string) $headerTotal) || !ctype_digit((string) $headerPages)) {
                    throw new RuntimeException('WordPress pagination headers are missing or invalid. Database unchanged.');
                }
                $currentTotal = (int) $headerTotal;
                $currentPages = (int) $headerPages;
                if ($currentTotal < 1 || $currentPages !== (int) ceil($currentTotal / 100)) {
                    throw new RuntimeException('WordPress returned an empty or inconsistent collection. Database unchanged.');
                }
                if ($total !== null && ($total !== $currentTotal || $pages !== $currentPages)) {
                    throw new RuntimeException('WordPress meeting totals changed during pagination. Retry later; database unchanged.');
                }
                $total = $currentTotal;
                $pages = $currentPages;
                $rows = $response->json();
                if (!is_array($rows) || !array_is_list($rows) || count($rows) !== min(100, $total - ($page - 1) * 100)) {
                    throw new RuntimeException('WordPress returned an incomplete page ' . $page . '. Database unchanged.');
                }
                array_push($source, ...$rows);
            }
            if (count($source) !== $total) {
                throw new RuntimeException('WordPress meeting count did not match pagination headers. Database unchanged.');
            }
        }
        if (!is_array($source) || !array_is_list($source) || !$source) {
            throw new RuntimeException('The source must be a nonempty complete list of meetings. Database unchanged.');
        }
        $ids = [];
        foreach ($source as $row) {
            if (!is_array($row) || !isset($row['id']) || !is_int($row['id']) || $row['id'] <= 0 || isset($ids[$row['id']])) {
                throw new RuntimeException('The source has missing, invalid or duplicate WordPress IDs. Database unchanged.');
            }
            $ids[$row['id']] = true;
        }
        return $source;
    }

    private function administrator(UserRepositoryInterface $users, bool $initialize): User
    {
        $username = (string) config('nasuomi.admin_username');
        $admin = $users->getByUsername($username);
        if (!$admin && $initialize && $username !== 'serveradmin') {
            $default = $users->getByUsername('serveradmin');
            if ($default?->isAdmin() && Hash::check(self::DEFAULT_PASSWORD, $default->password_string)) {
                $admin = $default;
            }
        }
        if (!$admin?->isAdmin()) {
            throw new RuntimeException('The configured Finnish synchronization administrator does not exist or is not a server administrator. Run BMLT migrations first.');
        }
        if (Hash::check(self::DEFAULT_PASSWORD, $admin->password_string)) {
            if (!$initialize) {
                throw new RuntimeException('Replace the initial administrator password with nasuomi:sync --initialize-only before syncing.');
            }
            $password = (string) config('nasuomi.initial_admin_password');
            if (mb_strlen($password) < 12 || $password === self::DEFAULT_PASSWORD) {
                throw new RuntimeException('NASUOMI_INITIAL_ADMIN_PASSWORD must contain at least 12 characters and differ from the installation default.');
            }
            request()->setUserResolver(fn () => $admin);
            $users->updatePassword($admin->id_bigint, $password);
            if ($admin->login_string !== $username) {
                $users->update($admin->id_bigint, ['login_string' => $username]);
            }
            $admin->refresh();
        }
        return $admin;
    }

    private function serviceBody(ServiceBodyRepositoryInterface $repository, ?User $admin, bool $dryRun): ?ServiceBody
    {
        $bodies = ServiceBody::query()->whereNull('root_server_id')->where('name_string', self::AREA_NAME)->get();
        if ($bodies->count() > 1 || ($bodies->isNotEmpty() && $bodies->first()->sb_type !== ServiceBody::SB_TYPE_AREA)) {
            throw new RuntimeException('Suomen alue must identify exactly one local Area Service Body (AS).');
        }
        if ($bodies->isNotEmpty()) {
            return $bodies->first();
        }
        if ($dryRun) {
            return null;
        }
        return $repository->create([
            'name_string' => self::AREA_NAME, 'description_string' => 'Suomen NA-kokoukset / nasuomi.org',
            'sb_type' => ServiceBody::SB_TYPE_AREA, 'sb_owner' => 0,
            'principal_user_bigint' => $admin->id_bigint, 'editors_string' => '',
            'uri_string' => 'https://www.nasuomi.org', 'sb_meeting_email' => '',
        ]);
    }

    private function prepareNativeFormats(): void
    {
        foreach (self::CUSTOM_FORMATS as $label => [$key, $type]) {
            $this->nativeFormats[$key] = ['label' => $label, 'type' => $type];
        }
    }

    private function formatIds(FormatRepositoryInterface $repository, bool $dryRun): array
    {
        $ids = [];
        $all = Format::query()->whereNull('root_server_id')->where('lang_enum', 'en')->get()->groupBy('key_string');
        foreach ($all as $key => $rows) {
            if ($rows->pluck('shared_id_bigint')->unique()->count() > 1) {
                throw new RuntimeException('Ambiguous BMLT format key: ' . $key);
            }
            $ids[$key] = $rows->first()->shared_id_bigint;
        }
        foreach ($this->nativeFormats as $key => $definition) {
            if (isset($ids[$key])) {
                continue;
            }
            if ($dryRun) {
                $ids[$key] = -count($ids) - 1;
                continue;
            }
            $label = $definition['label'];
            $translations = [['key_string' => $key, 'lang_enum' => 'en'], ['key_string' => $key === 'PAUSED' ? 'TAUOLLA' : $key, 'lang_enum' => 'fi']];
            $values = array_map(fn ($translation) => $translation + [
                'name_string' => $label, 'description_string' => $label,
                'format_type_enum' => $definition['type'], 'worldid_mixed' => $definition['type'] === 'LANG' ? 'LANG' : null,
            ], $translations);
            $ids[$key] = $repository->create($values)->shared_id_bigint;
        }
        return $ids;
    }

    private function normalize(array $row): array
    {
        $name = $this->plainText($this->string($row['title']['rendered'] ?? ''));
        $weekday = $this->string($row['weekday'] ?? null);
        $time = str_replace('.', ':', $this->string($row['alkamisaika'] ?? null));
        if (!$name || !array_key_exists($weekday, self::WEEKDAYS) || !preg_match('/^(\d{1,2}):([0-5]\d)$/', $time, $timeParts) || (int) $timeParts[1] > 23) {
            throw new RuntimeException('Missing name or valid weekly weekday/start time.');
        }
        $duration = $this->string($row['kesto'] ?? null);
        if ($duration === '' || !ctype_digit($duration) || (int) $duration >= 1440) {
            throw new RuntimeException('Duration must be numeric minutes (0 means unknown), below 24 hours.');
        }
        $minutes = (int) $duration;
        $street = $this->plainText($this->string($row['katuosoite'] ?? null));
        $city = $this->plainText($this->string($row['kaupunki'] ?? null));
        $postalCode = $this->string($row['postinumero'] ?? null);
        $country = $this->string($row['maa'] ?? null) ?: 'Suomi';
        $area = $this->string($row['area'] ?? null);
        $mapUrl = $this->string($row['karttalinkki'] ?? null);
        $virtual = $area === 'Internet' || mb_strtolower($city) === 'internet';
        $notes = $this->plainText($this->string($row['lisatiedot'] ?? null));
        $english = $this->plainText($this->string($row['lisatiedot_en'] ?? null));
        $virtualUrl = $virtual ? $mapUrl : '';
        if (!$virtual && preg_match('~https?://(?:[a-z0-9.-]+\.)?(?:zoom\.us|discord\.gg|discord\.com)/[^\s<>\)]+~iu', $notes, $match)) {
            $virtualUrl = rtrim($match[0], '.,');
        }
        $venue = $virtual ? Meeting::VENUE_TYPE_VIRTUAL : ($virtualUrl ? Meeting::VENUE_TYPE_HYBRID : Meeting::VENUE_TYPE_IN_PERSON);
        if ($virtual && !$this->publicUrl($virtualUrl)) {
            throw new RuntimeException('Virtual meeting has no usable join URL.');
        }
        if (!$virtual && (!$street || !$city)) {
            throw new RuntimeException('Physical meeting has no street address or city.');
        }
        $address = implode(', ', array_filter([$street, $postalCode, $city, $country], fn ($v) => $v !== ''));
        $coordinates = $virtual ? null : $this->coordinates($row, $address, $mapUrl);
        if (!$virtual && !$coordinates) {
            throw new RuntimeException('Unresolved map marker coordinates; no camera/map-center coordinates were substituted. Address: ' . $address);
        }
        $formatLabels = $this->relationLabels($this->string($row['rel_kokousmuodot'] ?? null), array_merge(array_keys(self::STOCK_FORMATS), array_keys(self::CUSTOM_FORMATS)));
        $languageLabels = $this->relationLabels($this->string($row['rel_kokouskielet'] ?? null), array_keys(self::LANGUAGES));
        $keys = [];
        foreach ($formatLabels as $label) {
            if (isset(self::STOCK_FORMATS[$label])) {
                array_push($keys, ...self::STOCK_FORMATS[$label]);
            } else {
                $keys[] = $this->nativeKey($label, $row);
            }
        }
        foreach ($languageLabels as $label) {
            $keys[] = self::LANGUAGES[$label] ?? $this->nativeKey($label, $row, 'LANG');
        }
        if (!$languageLabels) {
            $this->flag($row, 'warning', 'language_unknown', 'No meeting language was supplied; Finnish was not inferred.');
        }
        if ($virtual) {
            $keys[] = 'VM';
        } elseif ($venue === Meeting::VENUE_TYPE_HYBRID) {
            $keys[] = 'HY';
            $this->flag($row, 'warning', 'hybrid_from_notes', 'A physical venue and online join URL were found; verify hybrid participation.');
        }
        $pauseNotice = $this->pauseNotice($row);
        if ($pauseNotice) {
            $keys[] = 'PAUSED';
            $this->flag($row, 'warning', 'paused', $pauseNotice . ' Meeting remains published with Tauolla format.');
        }
        $comments = array_filter([$pauseNotice, $notes, $english ? "English:\n" . $english : '',
            $formatLabels ? 'Kokousmuodot: ' . implode('; ', $formatLabels) : '',
            $languageLabels ? 'Kokouskielet: ' . implode('; ', $languageLabels) : '',
        ], fn ($v) => $v !== '');
        $locationInfo = array_filter([
            !$virtual && $mapUrl ? 'Kartta: ' . $mapUrl : '',
            $this->string($row['link'] ?? null) ? 'Lähde: ' . $this->string($row['link']) : '',
            $area ? 'NA-palvelualue: ' . $area : '',
        ], fn ($v) => $v !== '');
        if (!$minutes) {
            $this->flag($row, 'information', 'duration_unknown', 'Stored as NULL. The legacy BMLT API returns the configured default duration for unknown duration.');
        }
        if (!$virtual && ($postalCode === '' || !preg_match('/^\d{5}$/', $postalCode))) {
            $this->flag($row, 'warning', 'postcode', $postalCode === '' ? 'Missing postcode; preserved without guessing. Admin API may require a province or postcode.' : 'Nonstandard postcode preserved: ' . $postalCode);
        }
        $timezone = $country === 'Espanja' ? 'Europe/Madrid' : 'Europe/Helsinki';
        if ($timezone === 'Europe/Madrid') {
            $this->flag($row, 'warning', 'timezone_provisional', 'Europe/Madrid assumes source start time is local in Spain; organizer confirmation is required.');
        }
        $values = [
            'source_id' => $row['id'], 'root_server_id' => null,
            'weekday_tinyint' => self::WEEKDAYS[$weekday], 'venue_type' => $venue,
            'start_time' => sprintf('%02d:%02d:00', (int) $timeParts[1], (int) $timeParts[2]),
            'duration_time' => $minutes ? sprintf('%02d:%02d:00', intdiv($minutes, 60), $minutes % 60) : null,
            'time_zone' => $timezone, 'lang_enum' => 'en', 'latitude' => $coordinates[0] ?? null, 'longitude' => $coordinates[1] ?? null,
            'published' => ($row['status'] ?? 'publish') === 'publish' ? 1 : 0,
            'worldid_mixed' => $this->string($row['naws_id'] ?? null) ?: null, 'email_contact' => null,
            'meeting_name' => $name, 'location_text' => $virtual ? 'Internet' : '',
            'location_street' => $virtual ? '' : $street, 'location_municipality' => $virtual ? '' : $city,
            'location_postal_code_1' => $virtual ? '' : $postalCode, 'location_nation' => $country,
            'location_info' => implode("\n", $locationInfo), 'comments' => implode("\n\n", $comments),
            'virtual_meeting_link' => $virtualUrl,
        ];
        foreach ($values as $field => $value) {
            if (!in_array($field, Meeting::$mainFields) && mb_strlen((string) $value) > ($field === 'meeting_name' ? 128 : 512)) {
                $this->flag($row, 'warning', 'admin_text_limit', $field . ' contains ' . mb_strlen((string) $value) . ' characters after normalization; preserved through longdata, but Admin API writes may reject it.');
            }
        }
        return ['values' => $values, 'format_keys' => array_values(array_unique($keys))];
    }

    private function nativeKey(string $label, array $row, ?string $type = null): string
    {
        if (isset(self::CUSTOM_FORMATS[$label])) {
            return self::CUSTOM_FORMATS[$label][0];
        }
        if (mb_strlen($label) > 255) {
            throw new RuntimeException('Unknown source format label is too long for a native BMLT format; the complete raw value is retained in the snapshot.');
        }
        $key = 'N' . strtoupper(substr(hash('sha256', $label), 0, 8));
        $this->nativeFormats[$key] = ['label' => $label, 'type' => $type];
        $this->flag($row, 'information', 'new_source_format', 'Preserved new label as native format ' . $key . ': ' . $label);
        return $key;
    }

    private function relationLabels(string $value, array $known): array
    {
        $labels = [];
        usort($known, fn ($a, $b) => strlen($b) <=> strlen($a));
        $value = trim($value);
        while ($value !== '') {
            $matched = null;
            foreach ($known as $label) {
                if (str_starts_with($value, $label) && preg_match('/^(?:$|\s*,\s*|\s+ja\s+)/u', substr($value, strlen($label)))) {
                    $matched = $label;
                    break;
                }
            }
            if ($matched === null) {
                // Preserve an unfamiliar remaining label verbatim, including internal commas.
                $labels[] = $value;
                break;
            }
            $labels[] = $matched;
            $value = trim(substr($value, strlen($matched)));
            $value = preg_replace('/^(?:,\s*(?:ja\s+)?|ja\s+)/u', '', $value);
        }
        return array_values(array_unique($labels));
    }

    private function pauseNotice(array $row): string
    {
        $indefinite = in_array(mb_strtolower($this->string($row['cancelled_for_now'] ?? null)), ['kyllä', 'kylla', 'true', '1'], true);
        $rawDate = $this->string($row['tauolla_pvm_asti'] ?? null);
        $date = null;
        if ($rawDate !== '') {
            $date = \DateTimeImmutable::createFromFormat('!d.m.Y', $rawDate, new \DateTimeZone('Europe/Helsinki'));
            if (!$date || $date->format('d.m.Y') !== $rawDate) {
                $this->flag($row, 'warning', 'pause_date_invalid', 'Unrecognized pause date retained in source snapshot: ' . $rawDate);
                $date = null;
            }
        }
        if ($indefinite) {
            return 'TAUOLLA: Kokous on tauolla toistaiseksi. Tarkista ajantasainen tilanne nasuomi.org-palvelusta.';
        }
        if ($date && $date->format('Y-m-d') > Carbon::today('Europe/Helsinki')->toDateString()) {
            return 'TAUOLLA: Kokous on tauolla ' . $rawDate . ' asti. Tarkista ajantasainen tilanne nasuomi.org-palvelusta.';
        }
        return '';
    }

    private function synchronize(MeetingRepositoryInterface $repository, ?ServiceBody $area, array $normalized, array $formatIds, bool $dryRun): array
    {
        $existing = $area ? Meeting::query()->with(['data', 'longdata'])->whereNull('root_server_id')
            ->where('service_body_bigint', $area->id_bigint)->whereNotNull('source_id')->get() : collect();
        if ($existing->pluck('source_id')->unique()->count() !== $existing->count()) {
            throw new RuntimeException('Duplicate source_id values exist inside Suomen alue. No meetings changed.');
        }
        $existing = $existing->keyBy('source_id');
        $counts = ['created' => 0, 'updated' => 0, 'deleted' => 0, 'unchanged' => 0, 'service_body_id' => $area?->id_bigint];
        foreach ($normalized as $sourceId => $record) {
            $values = $record['values'];
            $ids = [];
            foreach ($record['format_keys'] as $key) {
                if (!isset($formatIds[$key])) {
                    throw new RuntimeException('A required native BMLT format is missing: ' . $key);
                }
                $ids[] = $formatIds[$key];
            }
            sort($ids, SORT_NUMERIC);
            $values['formats'] = implode(',', array_unique($ids));
            $values['service_body_bigint'] = $area?->id_bigint;
            $meeting = $existing->get($sourceId);
            if (!$meeting) {
                $counts['created']++;
                if (!$dryRun) {
                    $repository->create($values);
                }
            } elseif ($this->unchanged($meeting, $values)) {
                $counts['unchanged']++;
            } else {
                $counts['updated']++;
                if (!$dryRun) {
                    $repository->update($meeting->id_bigint, $values);
                }
            }
        }
        // Source-invalid meetings are excluded along with genuinely removed source IDs.
        foreach ($existing as $sourceId => $meeting) {
            if (!isset($normalized[$sourceId])) {
                $counts['deleted']++;
                if (!$dryRun) {
                    $repository->delete($meeting->id_bigint);
                }
            }
        }
        return $counts;
    }

    private function unchanged(Meeting $meeting, array $values): bool
    {
        $data = $meeting->data->mapWithKeys(fn ($field) => [$field->key => $field->data_string])
            ->merge($meeting->longdata->mapWithKeys(fn ($field) => [$field->key => $field->data_blob]))->all();
        $expectedData = [];
        foreach ($values as $field => $expected) {
            if (!in_array($field, Meeting::$mainFields)) {
                $expectedData[$field] = $expected;
                continue;
            }
            $actual = $meeting->{$field};
            if (in_array($field, ['latitude', 'longitude']) && $actual !== null && $expected !== null) {
                if (abs($actual - $expected) > 0.0000001) {
                    return false;
                }
            } elseif ($actual !== $expected) {
                return false;
            }
        }
        ksort($data);
        ksort($expectedData);
        return $data === $expectedData;
    }

    private function loadCoordinates(): void
    {
        $cachePath = $this->stateDirectory . '/coordinates.json';
        $this->coordinateCache = file_exists($cachePath) ? json_decode($this->readFile($cachePath), true, 512, JSON_THROW_ON_ERROR) : [];
        if (!is_array($this->coordinateCache)) {
            throw new RuntimeException('Coordinate cache must be a JSON object.');
        }
        $path = config('nasuomi.coordinate_overrides');
        $this->overrides = $path ? json_decode($this->readFile($path), true, 512, JSON_THROW_ON_ERROR) : [];
        if (!is_array($this->overrides)) {
            throw new RuntimeException('Coordinate overrides must be a JSON object keyed by WordPress ID.');
        }
    }

    private function coordinates(array $row, string $address, string $url): ?array
    {
        if (isset($this->overrides[$row['id']])) {
            $override = $this->overrides[$row['id']];
            if (!is_array($override) || ($override['address'] ?? null) !== $address || !$this->validCoordinates($override['latitude'] ?? null, $override['longitude'] ?? null)) {
                $this->flag($row, 'warning', 'coordinate_override_invalid', 'Override address must match exactly and coordinates must be valid. Expected address: ' . $address);
                return null;
            }
            return [(float) $override['latitude'], (float) $override['longitude']];
        }
        if ($coordinate = $this->markerCoordinates($url)) {
            return $coordinate;
        }
        $cacheKey = hash('sha256', $row['id'] . '|' . $address . '|' . $url);
        $cached = $this->coordinateCache[$cacheKey] ?? null;
        if (is_array($cached) && (!empty($cached['coordinates']) || ($cached['checked_at'] ?? 0) > time() - 6 * 3600)) {
            return $cached['coordinates'];
        }
        $coordinate = null;
        $resolvedUrl = $url;
        try {
            for ($redirects = 0; $redirects < 6; $redirects++) {
                if (!$this->mapUrl($resolvedUrl)) {
                    break;
                }
                $request = Http::connectTimeout(5)->timeout(15)->withOptions(['allow_redirects' => false])
                    ->withHeaders(['User-Agent' => 'BMLT-Finnish-Meeting-Sync/1.0']);
                $response = $request->head($resolvedUrl);
                if ($response->status() === 405) {
                    $response = $request->get($resolvedUrl);
                }
                if ($response->redirect()) {
                    $location = $response->header('Location');
                    if (!$location) {
                        break;
                    }
                    $resolvedUrl = (string) \GuzzleHttp\Psr7\UriResolver::resolve(new \GuzzleHttp\Psr7\Uri($resolvedUrl), new \GuzzleHttp\Psr7\Uri($location));
                    if (!$this->mapUrl($resolvedUrl)) {
                        break;
                    }
                    if ($coordinate = $this->markerCoordinates($resolvedUrl)) {
                        break;
                    }
                    continue;
                }
                // HTML can contain unrelated places. Only the explicit redirect URL is trusted.
                break;
            }
        } catch (Throwable) {
            $this->flag($row, 'warning', 'map_lookup_failed', 'Map redirect lookup failed; add verified coordinates or retry.');
        }
        $this->coordinateCache[$cacheKey] = [
            'wp_id' => $row['id'], 'address' => $address, 'url' => $url,
            'resolved_url' => $resolvedUrl, 'coordinates' => $coordinate, 'checked_at' => time(),
        ];
        return $coordinate;
    }

    private function markerCoordinates(string $value): ?array
    {
        if (!$this->mapUrl($value)) {
            return null;
        }
        $decoded = html_entity_decode(rawurldecode($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $matches = [];
        preg_match_all('/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/', $decoded, $markers, PREG_SET_ORDER);
        foreach ($markers as $marker) {
            if ($this->validCoordinates($marker[1], $marker[2])) {
                $matches[$marker[1] . ',' . $marker[2]] = [(float) $marker[1], (float) $marker[2]];
            }
        }
        // q/query identify a target; ll and @lat,lng identify the viewport and are not markers.
        parse_str((string) parse_url($value, PHP_URL_QUERY), $query);
        foreach (['q', 'query'] as $key) {
            if (isset($query[$key]) && is_string($query[$key]) && preg_match('/^(?:loc:)?\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/', $query[$key], $marker) && $this->validCoordinates($marker[1], $marker[2])) {
                $matches[$marker[1] . ',' . $marker[2]] = [(float) $marker[1], (float) $marker[2]];
            }
        }
        return count($matches) === 1 ? reset($matches) : null;
    }

    private function validCoordinates(mixed $latitude, mixed $longitude): bool
    {
        return is_numeric($latitude) && is_numeric($longitude)
            && (float) $latitude >= -90 && (float) $latitude <= 90
            && (float) $longitude >= -180 && (float) $longitude <= 180
            && !((float) $latitude === 0.0 && (float) $longitude === 0.0);
    }

    private function mapUrl(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if (
            in_array($host, ['google.com', 'www.google.com', 'google.fi', 'www.google.fi'], true)
            && !preg_match('~^/maps(?:/|$)~', (string) parse_url($url, PHP_URL_PATH))
        ) {
            return false;
        }
        return $this->publicUrl($url) && in_array($host, [
            'maps.app.goo.gl', 'goo.gl', 'g.co', 'share.google', 'maps.google.com', 'maps.google.fi',
            'google.com', 'www.google.com', 'google.fi', 'www.google.fi', 'fonecta.fi', 'www.fonecta.fi',
        ], true);
    }

    private function publicUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);
    }

    private function plainText(string $html): string
    {
        if ($html === '') {
            return '';
        }
        $document = new DOMDocument();
        $document->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        $text = $this->nodeText($document);
        $text = str_replace(["\r", "\xc2\xa0"], ['', ' '], $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text);
        $text = preg_replace('/ *\n */u', "\n", $text);
        return trim(preg_replace('/\n{3,}/u', "\n\n", $text));
    }

    private function nodeText(DOMNode $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return $node->textContent;
        }
        if ($node instanceof DOMElement && in_array(strtolower($node->tagName), ['script', 'style'], true)) {
            return '';
        }
        if ($node instanceof DOMElement && strtolower($node->tagName) === 'img') {
            return $node->getAttribute('alt') ?: $node->getAttribute('data-emoji');
        }
        $text = '';
        foreach ($node->childNodes as $child) {
            $text .= $this->nodeText($child);
        }
        if ($node instanceof DOMElement) {
            $tag = strtolower($node->tagName);
            if ($tag === 'a') {
                $href = $node->getAttribute('href');
                if (preg_match('/^(https?:\/\/|mailto:|tel:)/i', $href) && trim($text) !== $href) {
                    $text = trim($text) . ' (' . $href . ')';
                }
            }
            if ($tag === 'br' || in_array($tag, ['p', 'div', 'li', 'tr', 'h1', 'h2', 'h3', 'h4', 'blockquote'], true)) {
                $text .= "\n";
            } elseif ($tag === 'td' || $tag === 'th') {
                $text .= ' ';
            }
        }
        return $text;
    }

    private function string(mixed $value): string
    {
        return is_string($value) || is_int($value) || is_float($value) ? trim((string) $value) : '';
    }

    private function flag(array $row, string $severity, string $code, string $reason): void
    {
        $this->flags[] = ['wp_id' => $row['id'], 'name' => $this->string($row['title']['rendered'] ?? null),
            'url' => $this->string($row['link'] ?? null), 'severity' => $severity, 'code' => $code, 'reason' => $reason];
    }

    private function prepareStateDirectory(): void
    {
        $this->stateDirectory = rtrim((string) config('nasuomi.state_dir'), '/');
        if ($this->stateDirectory === '') {
            throw new RuntimeException('NASUOMI_STATE_DIR must name a private writable directory outside the web document root.');
        }
        foreach ([$this->stateDirectory, $this->stateDirectory . '/snapshots', $this->stateDirectory . '/reports'] as $path) {
            if (!is_dir($path) && !mkdir($path, 0700, true) && !is_dir($path)) {
                throw new RuntimeException('Cannot create synchronization state directory.');
            }
        }
    }

    private function readFile(string $path): string
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException('Cannot read synchronization input file: ' . $path);
        }
        return $contents;
    }

    private function writeJson(string $relativePath, array $values): void
    {
        $path = $this->stateDirectory . '/' . $relativePath;
        $temporary = $path . '.tmp';
        if (file_put_contents($temporary, json_encode($values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), LOCK_EX) === false || !rename($temporary, $path)) {
            throw new RuntimeException('Cannot write synchronization state: ' . $relativePath);
        }
        chmod($path, 0600);
    }

    private function pruneState(): void
    {
        foreach (['snapshots', 'reports'] as $directory) {
            foreach (glob($this->stateDirectory . '/' . $directory . '/*-*.json') ?: [] as $path) {
                if (is_file($path) && filemtime($path) < time() - self::RETENTION_SECONDS) {
                    unlink($path);
                }
            }
        }
    }
}
