<?php

namespace Tests\Feature;

use App\FromDatabaseConfig;
use App\Interfaces\MeetingRepositoryInterface;
use App\Models\Change;
use App\Models\Format;
use App\Models\Meeting;
use App\Models\ServiceBody;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Mockery;
use RuntimeException;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

class NasuomiSyncTest extends TestCase
{
    use RefreshDatabase;

    private string $stateDirectory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stateDirectory = sys_get_temp_dir() . '/nasuomi-test-' . bin2hex(random_bytes(8));
        config([
            'nasuomi.source_url' => 'https://source.example/wp-json/wp/v2/kokoukset',
            'nasuomi.state_dir' => $this->stateDirectory,
            'nasuomi.initial_admin_password' => 'test-initial-password',
            'nasuomi.coordinate_overrides' => null,
            'nasuomi.alert_email' => null,
        ]);
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->stateDirectory);
        FromDatabaseConfig::reset();
        parent::tearDown();
    }

    private function sourceMeeting(int $id, array $overrides = []): array
    {
        return array_replace([
            'id' => $id,
            'status' => 'publish',
            'title' => ['rendered' => 'Yhteinen nimi'],
            'link' => 'https://www.nasuomi.org/kokous/test-' . $id . '/',
            'area' => 'Etelä',
            'weekday' => 'Maanantai',
            'alkamisaika' => '18.00',
            'kesto' => '90',
            'cancelled_for_now' => '',
            'tauolla_pvm_asti' => '',
            'katuosoite' => 'Testikatu 1',
            'postinumero' => '00100',
            'kaupunki' => 'Helsinki',
            'maa' => '',
            'karttalinkki' => 'https://www.google.com/maps/place/Test/data=!3d60.1708!4d24.9375',
            'lisatiedot' => '<p>Tervetuloa.</p>',
            'lisatiedot_en' => '',
            'rel_kokouskielet' => 'suomi',
            'rel_kokousmuodot' => false,
            'naws_id' => '',
        ], $overrides);
    }

    private function sourceSnapshot(array $meetings): void
    {
        $this->resetHttp();
        Http::fake([
            'https://source.example/*' => Http::response($meetings, 200, [
                'X-WP-Total' => count($meetings),
                'X-WP-TotalPages' => 1,
            ]),
        ]);
    }

    private function resetHttp(): void
    {
        Http::swap(new Factory());
        Http::preventStrayRequests();
    }

    private function meetingText(Meeting $meeting, string $key): string
    {
        return $meeting->data()->where('key', $key)->value('data_string')
            ?? $meeting->longdata()->where('key', $key)->value('data_blob')
            ?? '';
    }

    private function embeddedMapHtml(array $places, array $additionalPlaces = []): string
    {
        $state = array_fill(0, 22, null);
        $state[21] = [[[62.9, 27.9]], null, null, $places];
        $state[4] = $additionalPlaces;
        return '<script>initEmbed(' . json_encode($state, JSON_THROW_ON_ERROR) . ');</script>';
    }

    private function initializeMirror(): void
    {
        $this->assertSame(0, Artisan::call('nasuomi:sync', ['--initialize-only' => true]));
    }

    public function testDryRunDoesNotInitializeOrImport(): void
    {
        $this->sourceSnapshot([$this->sourceMeeting(101)]);
        $formats = Format::count();
        $password = User::where('login_string', 'serveradmin')->value('password_string');

        $this->assertSame(0, Artisan::call('nasuomi:sync', ['--dry-run' => true]));

        $this->assertSame(0, Meeting::count());
        $this->assertSame(0, ServiceBody::count());
        $this->assertSame($formats, Format::count());
        $this->assertSame($password, User::where('login_string', 'serveradmin')->value('password_string'));
    }

    public function testInitializationIsIdempotentAndPreservesChangedPassword(): void
    {
        $this->assertSame(0, Artisan::call('nasuomi:sync', ['--initialize-only' => true]));
        $user = User::where('login_string', 'serveradmin')->firstOrFail();
        $this->assertTrue(Hash::check('test-initial-password', $user->password_string));
        $this->assertSame('AS', ServiceBody::where('name_string', 'Suomen alue')->value('sb_type'));
        $formats = Format::count();
        $customPassword = Hash::make('changed-by-administrator');
        $user->update(['password_string' => $customPassword]);

        $this->assertSame(0, Artisan::call('nasuomi:sync', ['--initialize-only' => true]));

        $this->assertSame(1, ServiceBody::count());
        $this->assertSame($formats, Format::count());
        $this->assertSame($customPassword, $user->fresh()->password_string);
        Http::assertNothingSent();
    }

    public function testAlertsAreOptInAndInitializationSuccessfulSyncAndDryRunsStaySilent(): void
    {
        Mail::shouldReceive('raw')->never();
        config(['nasuomi.alert_email' => 'alerts@example.org']);
        $this->initializeMirror();
        $this->sourceSnapshot([$this->sourceMeeting(101)]);
        $this->assertSame(0, Artisan::call('nasuomi:sync'));
        $this->assertSame(0, Artisan::call('nasuomi:sync', ['--dry-run' => true]));
        $this->sourceSnapshot([$this->sourceMeeting(101, ['weekday' => ''])]);
        $this->assertSame(2, Artisan::call('nasuomi:sync', ['--dry-run' => true]));
        $this->resetHttp();
        Http::fake(['https://source.example/*' => Http::response('', 503)]);
        $this->assertSame(1, Artisan::call('nasuomi:sync', ['--dry-run' => true]));
        config(['nasuomi.alert_email' => null]);
        $this->assertSame(1, Artisan::call('nasuomi:sync'));
    }

    public function testFailedFetchSendsOnePlainAlertAfterWritingReportWithoutChangingMeetings(): void
    {
        config(['nasuomi.alert_email' => 'alerts@example.org']);
        $this->initializeMirror();
        $this->sourceSnapshot([$this->sourceMeeting(101)]);
        $this->assertSame(0, Artisan::call('nasuomi:sync'));
        $meetingId = Meeting::firstOrFail()->id_bigint;
        $changes = Change::count();
        $this->resetHttp();
        Http::fake(['https://source.example/*' => Http::response(['private' => 'raw-source-must-not-appear'], 503)]);
        $sent = [];
        Mail::shouldReceive('raw')->once()->andReturnUsing(function ($text, $callback) use (&$sent) {
            $message = new Message(new Email());
            $callback($message);
            preg_match('/^Report: (.+)$/m', $text, $matches);
            $sent = ['text' => $text, 'message' => $message->getSymfonyMessage(),
                'report_exists' => isset($matches[1]) && is_file($matches[1])];
        });

        $this->assertSame(1, Artisan::call('nasuomi:sync'));

        $this->assertSame($meetingId, Meeting::firstOrFail()->id_bigint);
        $this->assertSame($changes, Change::count());
        $this->assertTrue($sent['report_exists']);
        $this->assertSame('alerts@example.org', $sent['message']->getTo()[0]->getAddress());
        $this->assertSame('Finnish BMLT sync failed', $sent['message']->getSubject());
        $this->assertStringContainsString('Time (UTC): ', $sent['text']);
        $this->assertStringContainsString('Source meetings: unavailable', $sent['text']);
        $this->assertStringContainsString('WordPress fetch failed on page 1 (HTTP 503)', $sent['text']);
        $this->assertStringNotContainsString('raw-source-must-not-appear', $sent['text']);
        $this->assertStringNotContainsString('test-initial-password', $sent['text']);
        $this->assertStringNotContainsString('Tervetuloa.', $sent['text']);
    }

    public function testPartialSyncSendsOneBoundedSkippedSummaryAfterCommittingUsableMeetings(): void
    {
        config(['nasuomi.alert_email' => 'alerts@example.org']);
        $this->initializeMirror();
        $source = array_map(fn ($id) => $this->sourceMeeting($id, ['weekday' => '']), range(101, 112));
        $source[] = $this->sourceMeeting(113);
        $this->sourceSnapshot($source);
        $sent = [];
        Mail::shouldReceive('raw')->once()->andReturnUsing(function ($text, $callback) use (&$sent) {
            $message = new Message(new Email());
            $callback($message);
            preg_match('/^Report: (.+)$/m', $text, $matches);
            $sent = ['text' => $text, 'message' => $message->getSymfonyMessage(),
                'report' => json_decode(File::get($matches[1]), true, 512, JSON_THROW_ON_ERROR),
                'meetings' => Meeting::pluck('source_id')->all()];
        });

        $this->assertSame(2, Artisan::call('nasuomi:sync'));

        $this->assertSame([113], $sent['meetings']);
        $this->assertSame('incomplete', $sent['report']['status']);
        $this->assertSame(1, $sent['report']['created']);
        $this->assertSame(12, $sent['report']['skipped']);
        $this->assertSame('Finnish BMLT sync incomplete', $sent['message']->getSubject());
        $this->assertStringContainsString('Source meetings: 13', $sent['text']);
        $this->assertStringContainsString('Imported: 1; skipped: 12', $sent['text']);
        $this->assertStringContainsString('Created: 1; updated: 0; deleted: 0; unchanged: 0.', $sent['text']);
        $this->assertStringContainsString('Skipped WP 101:', $sent['text']);
        $this->assertStringContainsString('Skipped WP 110:', $sent['text']);
        $this->assertStringNotContainsString('Skipped WP 111:', $sent['text']);
        $this->assertStringContainsString('2 more skipped meetings are listed in the local report.', $sent['text']);
        $this->assertStringNotContainsString('Tervetuloa.', $sent['text']);
    }

    public function testMailTransportFailurePreservesPartialSyncExitStatusReportAndCommittedMeeting(): void
    {
        config(['nasuomi.alert_email' => 'alerts@example.org']);
        $this->initializeMirror();
        $this->sourceSnapshot([$this->sourceMeeting(101), $this->sourceMeeting(102, ['weekday' => ''])]);
        Mail::shouldReceive('raw')->once()->andThrow(new RuntimeException('SMTP sensitive transport details'));

        $this->assertSame(2, Artisan::call('nasuomi:sync'));

        $this->assertSame([101], Meeting::pluck('source_id')->all());
        $output = Artisan::output();
        $this->assertStringContainsString('Could not send sync alert email.', $output);
        $this->assertStringNotContainsString('SMTP sensitive transport details', $output);
        preg_match('/Report: (.+)/', $output, $matches);
        $report = json_decode(File::get(trim($matches[1])), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('incomplete', $report['status']);
        $this->assertSame(1, $report['created']);
        $this->assertSame(1, $report['skipped']);
    }

    public function testImportPreservesIdentityNotesAndUnknownDurationAndRerunMakesNoChanges(): void
    {
        $this->initializeMirror();
        $notes = '<p>' . str_repeat('Pitkä lisätieto. ', 60) . '</p>'
            . '<p><a href="https://example.org/info">Ohjeet</a> '
            . '<img alt="🔥" src="https://example.org/fire.png"></p>';
        $this->sourceSnapshot([
            $this->sourceMeeting(101, ['kesto' => '0', 'lisatiedot' => $notes]),
            $this->sourceMeeting(102, ['weekday' => 'Sunnuntai', 'cancelled_for_now' => 'Kyllä']),
        ]);

        $this->assertSame(0, Artisan::call('nasuomi:sync'));
        $this->assertSame(2, Meeting::count());
        $first = Meeting::where('source_id', 101)->firstOrFail();
        $second = Meeting::where('source_id', 102)->firstOrFail();
        $this->assertNull($first->duration_time);
        $this->assertNull($first->root_server_id);
        $this->assertEmpty($first->worldid_mixed);
        $this->assertSame(1, $first->weekday_tinyint);
        $this->assertSame(0, $second->weekday_tinyint);
        $this->assertSame('Europe/Helsinki', $first->time_zone);
        $this->assertSame(1, $second->published);
        $this->assertSame('Lähde: https://www.nasuomi.org/kokous/test-101/', $this->meetingText($first, 'comments'));
        $this->assertSame('Lähde: https://www.nasuomi.org/kokous/test-102/', $this->meetingText($second, 'comments'));
        $this->assertStringContainsString('https://example.org/info', $this->meetingText($first, 'location_info'));
        $this->assertStringContainsString('🔥', $this->meetingText($first, 'location_info'));
        $this->assertStringNotContainsString('<p>', $this->meetingText($first, 'location_info'));
        $this->assertSame('Tervetuloa.', $this->meetingText($second, 'location_info'));
        $this->assertSame(1, $first->longdata()->where('key', 'location_info')->count());
        $pausedFormat = (string) Format::where('lang_enum', 'en')->where('key_string', 'PAUSED')->value('shared_id_bigint');
        $this->assertContains($pausedFormat, explode(',', $second->formats));
        $changes = Change::count();
        $ids = Meeting::orderBy('source_id')->pluck('id_bigint')->all();

        $this->assertSame(0, Artisan::call('nasuomi:sync'));
        $this->assertSame($ids, Meeting::orderBy('source_id')->pluck('id_bigint')->all());
        $this->assertSame($changes, Change::count());
    }

    public function testSourceNotesUseSingleSpacesWithoutAppendingOtherSourceFields(): void
    {
        $this->initializeMirror();
        $notes = "<p>Ensimmäinen\n\t kappale.</p><p>Toinen<br>Kolmas</p>"
            . '<ul><li>Kohta <strong>yksi</strong></li><li>Kohta kaksi</li></ul>'
            . 'prefix<div>body</div>suffix ab<strong>cd</strong>ef'
            . '<p><a href="https://example.org/info">Ohjeet</a> '
            . '<img alt="🔥" src="https://example.org/fire.png"></p>';
        $this->sourceSnapshot([$this->sourceMeeting(101, [
            'lisatiedot' => $notes,
            'lisatiedot_en' => 'English information must not be appended.',
            'area' => 'Pohjoinen',
            'rel_kokousmuodot' => 'Avoin kuun viimeinen',
            'rel_kokouskielet' => 'englanti ja suomi',
            'cancelled_for_now' => 'Kyllä',
        ])]);

        $this->assertSame(0, Artisan::call('nasuomi:sync'));

        $meeting = Meeting::where('source_id', 101)->firstOrFail();
        $this->assertSame('Lähde: https://www.nasuomi.org/kokous/test-101/', $this->meetingText($meeting, 'comments'));
        $this->assertSame(
            'Ensimmäinen kappale. Toinen Kolmas Kohta yksi Kohta kaksi prefix body suffix abcdef '
                . 'Ohjeet (https://example.org/info) 🔥',
            $this->meetingText($meeting, 'location_info')
        );
        $keys = Format::whereIn('shared_id_bigint', explode(',', $meeting->formats))
            ->where('lang_enum', 'en')->pluck('key_string')->all();
        $this->assertContains('PAUSED', $keys);
        $this->assertContains('OLAST', $keys);
        $this->assertContains('FIN', $keys);
        $this->assertContains('ENG', $keys);
    }

    public function testChangedSourceReplacesLegacyMixedTextAndRerunMakesNoChanges(): void
    {
        $this->initializeMirror();
        $source = $this->sourceMeeting(101, ['cancelled_for_now' => 'Kyllä']);
        $this->sourceSnapshot([$source]);
        $this->assertSame(0, Artisan::call('nasuomi:sync'));
        $meeting = Meeting::where('source_id', 101)->firstOrFail();
        $originalId = $meeting->id_bigint;
        $comments = $meeting->data()->where('key', 'comments')->firstOrFail();
        $legacyValues = $comments->only(['meetingid_bigint', 'key', 'field_prompt', 'lang_enum', 'visibility']);
        $legacyValues['data_blob'] = str_repeat(
            'TAUOLLA. Vanhat lisätiedot. English: old text. Kokouskielet: suomi. ',
            5
        );
        $meeting->longdata()->create($legacyValues);
        $comments->delete();
        $this->assertSame(1, $meeting->longdata()->where('key', 'comments')->count());
        $this->assertSame(1, $meeting->data()->where('key', 'location_info')->update([
            'data_string' => 'https://maps.example/old Lähde: old-link NA-alue: Etelä',
        ]));
        $source['link'] = 'https://www.nasuomi.org/kokous/updated-101/';
        $source['lisatiedot'] = '<p>Uudet <strong>lisätiedot</strong>.</p>'
            . '<p><a href="https://example.org/new">Uusi ohje</a> 🔥</p>';
        $this->sourceSnapshot([$source]);
        $changesBeforeUpdate = Change::count();

        $this->assertSame(0, Artisan::call('nasuomi:sync'));

        $updated = Meeting::where('source_id', 101)->firstOrFail();
        $this->assertSame($originalId, $updated->id_bigint);
        $this->assertSame('Lähde: https://www.nasuomi.org/kokous/updated-101/', $this->meetingText($updated, 'comments'));
        $this->assertSame('Uudet lisätiedot. Uusi ohje (https://example.org/new) 🔥', $this->meetingText($updated, 'location_info'));
        $this->assertSame(0, $updated->longdata()->where('key', 'comments')->count());
        $this->assertSame(1, $updated->data()->where('key', 'comments')->count());
        $this->assertGreaterThan($changesBeforeUpdate, Change::count());
        $changes = Change::count();

        $this->assertSame(0, Artisan::call('nasuomi:sync'));
        $this->assertSame(1, Meeting::count());
        $this->assertSame($originalId, Meeting::where('source_id', 101)->value('id_bigint'));
        $this->assertSame($changes, Change::count());
    }

    public function testSyncUpdatesAndDeletesOnlyOwnedRecords(): void
    {
        $this->initializeMirror();
        $this->sourceSnapshot([$this->sourceMeeting(101), $this->sourceMeeting(102)]);
        $this->assertSame(0, Artisan::call('nasuomi:sync'));
        $originalId = Meeting::where('source_id', 101)->value('id_bigint');
        $otherArea = ServiceBody::create([
            'name_string' => 'Other area', 'description_string' => '', 'sb_type' => 'AS',
            'sb_meeting_email' => '',
        ]);
        $unrelated = Meeting::create([
            'service_body_bigint' => $otherArea->id_bigint, 'source_id' => 102,
        ]);
        $manual = Meeting::create([
            'service_body_bigint' => ServiceBody::where('name_string', 'Suomen alue')->value('id_bigint'),
        ]);
        $this->sourceSnapshot([$this->sourceMeeting(101, ['alkamisaika' => '19:30'])]);

        $this->assertSame(0, Artisan::call('nasuomi:sync'));

        $this->assertSame($originalId, Meeting::where('source_id', 101)->value('id_bigint'));
        $this->assertSame('19:30:00', Meeting::where('source_id', 101)->value('start_time'));
        $this->assertNotNull($unrelated->fresh());
        $this->assertNotNull($manual->fresh());
        $this->assertSame(1, Meeting::where('source_id', 102)->count());
    }

    public function testIncompleteFetchAndEmptySnapshotLeaveMeetingsUnchanged(): void
    {
        $this->initializeMirror();
        $this->sourceSnapshot([$this->sourceMeeting(101)]);
        $this->assertSame(0, Artisan::call('nasuomi:sync'));
        $changes = Change::count();
        $this->resetHttp();
        Http::fakeSequence('https://source.example/*')
            ->push(array_map(fn ($id) => $this->sourceMeeting($id), range(102, 201)), 200, [
                'X-WP-Total' => 101, 'X-WP-TotalPages' => 2,
            ])
            ->push(['message' => 'Source unavailable'], 503);

        $this->assertNotSame(0, Artisan::call('nasuomi:sync'));
        $this->assertSame([101], Meeting::pluck('source_id')->all());
        $this->assertSame($changes, Change::count());

        $this->sourceSnapshot([]);
        $this->assertNotSame(0, Artisan::call('nasuomi:sync'));
        $this->assertSame([101], Meeting::pluck('source_id')->all());
        $this->assertSame($changes, Change::count());
    }

    public function testAllPagesAreImportedAndDuplicateSourceIdsAbortTheSync(): void
    {
        $this->initializeMirror();
        $firstPage = array_map(fn ($id) => $this->sourceMeeting($id), range(1, 100));
        $headers = ['X-WP-Total' => 101, 'X-WP-TotalPages' => 2];
        Http::fakeSequence('https://source.example/*')
            ->push($firstPage, 200, $headers)
            ->push([$this->sourceMeeting(101)], 200, $headers);

        $this->assertSame(0, Artisan::call('nasuomi:sync'));
        $this->assertSame(101, Meeting::count());
        $changes = Change::count();

        $this->resetHttp();
        Http::fakeSequence('https://source.example/*')
            ->push($firstPage, 200, $headers)
            ->push([$this->sourceMeeting(1)], 200, $headers);

        $this->assertNotSame(0, Artisan::call('nasuomi:sync'));
        $this->assertSame(101, Meeting::count());
        $this->assertSame($changes, Change::count());
    }

    public function testInvalidOwnedMeetingIsRemovedWhileUsableMeetingsContinue(): void
    {
        $this->initializeMirror();
        $this->sourceSnapshot([$this->sourceMeeting(101)]);
        $this->assertSame(0, Artisan::call('nasuomi:sync'));
        $this->sourceSnapshot([
            $this->sourceMeeting(101, ['weekday' => '']),
            $this->sourceMeeting(102),
        ]);

        $this->assertNotSame(0, Artisan::call('nasuomi:sync'));
        $this->assertSame([102], Meeting::pluck('source_id')->all());
    }

    public function testCameraCoordinatesAreRejectedButVirtualMeetingNeedsNone(): void
    {
        $this->initializeMirror();
        $this->sourceSnapshot([
            $this->sourceMeeting(101, [
                'karttalinkki' => 'https://www.google.com/maps/@60.1,24.9,15z',
            ]),
            $this->sourceMeeting(102, [
                'area' => 'Internet', 'kaupunki' => 'Internet', 'katuosoite' => 'Zoom',
                'karttalinkki' => 'https://us05web.zoom.us/j/123456789',
            ]),
        ]);

        $this->assertNotSame(0, Artisan::call('nasuomi:sync'));

        $this->assertSame([102], Meeting::pluck('source_id')->all());
        $meeting = Meeting::firstOrFail();
        $this->assertNull($meeting->latitude);
        $this->assertNull($meeting->longitude);
        $this->assertSame(Meeting::VENUE_TYPE_VIRTUAL, $meeting->venue_type);
    }

    public function testShareAndFonectaLinksUseTheirSelectedPlaceInsteadOfCameraOrSourceAddress(): void
    {
        $this->initializeMirror();
        $linkedAddress = 'Linked venue 4, 00200 Helsinki';
        $shareUrl = 'https://share.google/linked-place';
        $fonectaUrl = 'https://www.fonecta.fi/kartat/veturitallinpolku%204%20pori'
            . '?lon=21.881139278411865&lat=61.51392553125196&z=15';
        $this->sourceSnapshot([
            $this->sourceMeeting(101, ['karttalinkki' => $shareUrl, 'postinumero' => '99999']),
            $this->sourceMeeting(102, ['karttalinkki' => $fonectaUrl]),
        ]);
        Http::fake([
            $shareUrl => Http::response('', 302, [
                'Location' => 'https://www.google.com/search?q=' . rawurlencode($linkedAddress),
            ]),
            'https://maps.google.com/maps*' => function ($request) use ($linkedAddress) {
                parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
                $point = ($query['q'] ?? '') === $linkedAddress ? [60.2586841, 24.8597635] : [61.5, 21.8];
                return Http::response($this->embeddedMapHtml([['0x1:0x2', $query['q'] ?? '', $point]]));
            },
        ]);

        $this->assertSame(0, Artisan::call('nasuomi:sync'));

        $meeting = Meeting::where('source_id', 101)->firstOrFail();
        $this->assertEquals(60.2586841, $meeting->latitude);
        $this->assertEquals(24.8597635, $meeting->longitude);
        $this->assertSame('Testikatu 1', $this->meetingText($meeting, 'location_street'));
        $this->assertSame('99999', $this->meetingText($meeting, 'location_postal_code_1'));
        $fonecta = Meeting::where('source_id', 102)->firstOrFail();
        $this->assertEquals(61.5, $fonecta->latitude);
        $this->assertEquals(21.8, $fonecta->longitude);
        Http::assertSent(fn ($request) => $request->method() === 'GET' && $request->url() === $shareUrl);
        foreach ([$linkedAddress, 'veturitallinpolku 4 pori'] as $address) {
            Http::assertSent(function ($request) use ($address) {
                parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
                return $request->method() === 'GET'
                    && parse_url($request->url(), PHP_URL_HOST) === 'maps.google.com'
                    && $query === ['output' => 'embed', 'q' => $address];
            });
        }
        Http::assertSentCount(4);
    }

    public function testEmptyOriginRouteAcceptsOneDestinationButRejectsOriginAndMultiplePoints(): void
    {
        $this->initializeMirror();
        $point = '!2m2!1d24.8597635!2d60.2586841';
        $this->sourceSnapshot([
            $this->sourceMeeting(101, ['karttalinkki' => 'https://www.google.com/maps/dir//Venue/data=' . $point]),
            $this->sourceMeeting(102, ['karttalinkki' => 'https://www.google.com/maps/dir/Origin/Venue/data=' . $point]),
            $this->sourceMeeting(103, ['karttalinkki' => 'https://www.google.com/maps/dir//Venue/data=' . $point
                . '!2m2!1d25.1!2d61.1']),
            $this->sourceMeeting(104, ['karttalinkki' => 'https://www.google.com/maps/dir/?api=1&origin=60.1,24.9']),
        ]);
        Http::fake(['https://www.google.com/maps/dir/*' => Http::response('')]);

        $this->assertSame(2, Artisan::call('nasuomi:sync'));

        $this->assertSame([101], Meeting::pluck('source_id')->all());
        $meeting = Meeting::firstOrFail();
        $this->assertEquals(60.2586841, $meeting->latitude);
        $this->assertEquals(24.8597635, $meeting->longitude);
        Http::assertNotSent(fn ($request) => parse_url($request->url(), PHP_URL_HOST) === 'maps.google.com');
    }

    public function testPathAndQueryFeatureIdsUseExactUnsignedCidWithoutAddressQuery(): void
    {
        $this->initializeMirror();
        $featureId = '0x1:0xc89e2a5432e461f6';
        $this->sourceSnapshot([
            $this->sourceMeeting(101, ['karttalinkki' => 'https://www.google.com/maps/place/Nearby/data=!1s'
                . $featureId . '?q=Nearby+wrong+address']),
            $this->sourceMeeting(102, ['karttalinkki' => 'https://www.google.com/search?q=Nearby+wrong+address&ftid='
                . rawurlencode($featureId)]),
        ]);
        Http::fake([
            'https://maps.google.com/maps*' => function ($request) {
                parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
                $point = $query === ['output' => 'embed', 'cid' => '14456038395025318390']
                    ? [61.4830728, 23.7985248] : [61.49, 23.8];
                // Google may canonicalize the requested CID to a different place identifier.
                return Http::response($this->embeddedMapHtml([['0x3:0x4', 'Actual linked venue', $point]]));
            },
        ]);

        $this->assertSame(0, Artisan::call('nasuomi:sync'));

        foreach (Meeting::all() as $meeting) {
            $this->assertEquals(61.4830728, $meeting->latitude);
            $this->assertEquals(23.7985248, $meeting->longitude);
        }
        $requests = Http::recorded(fn ($request) => parse_url($request->url(), PHP_URL_HOST) === 'maps.google.com');
        $this->assertCount(2, $requests);
        foreach ($requests as [$request]) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
            $this->assertSame(['output' => 'embed', 'cid' => '14456038395025318390'], $query);
        }
    }

    public function testEmbedRejectsAdditionalPlacesAndViewportOnlyResults(): void
    {
        $this->initializeMirror();
        $this->sourceSnapshot([
            $this->sourceMeeting(101, ['karttalinkki' => 'https://www.google.com/maps/search/Ambiguous']),
            $this->sourceMeeting(102, ['karttalinkki' => 'https://www.google.com/maps/search/Viewport+only']),
        ]);
        Http::fake([
            'https://maps.google.com/maps*' => function ($request) {
                parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
                $html = ($query['q'] ?? '') === 'Ambiguous'
                    ? $this->embeddedMapHtml(
                        [['0x1:0x2', 'Selected candidate', [60.1708, 24.9375]]],
                        [['0x3:0x4', 'Another candidate elsewhere in the page state', [61.5, 21.8]]]
                    ) : $this->embeddedMapHtml([]);
                return Http::response($html);
            },
        ]);

        $this->assertSame(2, Artisan::call('nasuomi:sync'));
        $this->assertSame(0, Meeting::count());
        Http::assertSentCount(3);
    }

    public function testUntrustedMapHostsAndRedirectsCannotSupplyCoordinates(): void
    {
        $this->initializeMirror();
        $untrustedUrl = 'https://www.google.com.evil.example/maps/place/Test/data=!3d60.1708!4d24.9375';
        $shareUrl = 'https://share.google/untrusted-redirect';
        $this->sourceSnapshot([
            $this->sourceMeeting(101, ['karttalinkki' => $untrustedUrl]),
            $this->sourceMeeting(102, ['karttalinkki' => $shareUrl]),
        ]);
        Http::fake([
            $shareUrl => Http::response('', 302, ['Location' => $untrustedUrl]),
            $untrustedUrl => Http::response($this->embeddedMapHtml([['0x1:0x2', 'Untrusted place', [60.1708, 24.9375]]])),
        ]);

        $this->assertSame(2, Artisan::call('nasuomi:sync'));
        $this->assertSame(0, Meeting::count());
        Http::assertNotSent(fn ($request) => $request->url() === $untrustedUrl);
        Http::assertSentCount(2);
    }

    public function testLegacyNegativeCoordinatesAreRetriedAndPositiveCacheMakesRerunUnchanged(): void
    {
        $this->initializeMirror();
        $shareUrl = 'https://share.google/previously-unresolved';
        $address = 'Testikatu 1, 00100, Helsinki, Suomi';
        $cacheKey = hash('sha256', '101|' . $address . '|' . $shareUrl);
        File::put($this->stateDirectory . '/coordinates.json', json_encode([$cacheKey => [
            'wp_id' => 101, 'address' => $address, 'url' => $shareUrl,
            'coordinates' => null, 'checked_at' => time(),
        ]], JSON_THROW_ON_ERROR));
        $source = $this->sourceMeeting(101, ['karttalinkki' => $shareUrl]);
        $this->sourceSnapshot([$source]);
        Http::fake([
            $shareUrl => Http::response('', 302, ['Location' => 'https://www.google.com/search?q=Linked+venue']),
            'https://maps.google.com/maps*' => Http::response(
                $this->embeddedMapHtml([['0x1:0x2', 'Linked venue', [60.2586841, 24.8597635]]])
            ),
        ]);

        $this->assertSame(0, Artisan::call('nasuomi:sync'));
        Http::assertSentCount(3);
        $meeting = Meeting::firstOrFail();
        $this->assertEquals(60.2586841, $meeting->latitude);
        $cache = json_decode(File::get($this->stateDirectory . '/coordinates.json'), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(2, $cache[$cacheKey]['resolver_version']);
        $this->assertSame([60.2586841, 24.8597635], $cache[$cacheKey]['coordinates']);
        $changes = Change::count();
        $this->sourceSnapshot([$source]);

        $this->assertSame(0, Artisan::call('nasuomi:sync'));
        $this->assertSame($meeting->id_bigint, Meeting::firstOrFail()->id_bigint);
        $this->assertSame($changes, Change::count());
        Http::assertSentCount(1);
    }

    public function testSourceLabelsPreserveConditionalOpennessAndCommaInsideLabel(): void
    {
        $this->initializeMirror();
        $this->assertFalse(Format::where('key_string', 'OBSTRUCT')->exists());
        $this->assertFalse(Format::where('name_string', 'Esteellinen')->exists());
        $this->sourceSnapshot([$this->sourceMeeting(101, [
            'rel_kokousmuodot' => 'Avoin kuun viimeinen, Avoin vuosipäivinä, ja Esteetön pääsy',
            'rel_kokouskielet' => 'englanti ja suomi',
        ]), $this->sourceMeeting(102, [
            'rel_kokousmuodot' => 'Ryhmässä kerran kuussa alustaja, joka jakaa kokemustaan n.15min'
                . ' ja Tila ei ole esteetön',
        ])]);

        $this->assertSame(0, Artisan::call('nasuomi:sync'));

        $meeting = Meeting::where('source_id', 101)->firstOrFail();
        $keys = Format::whereIn('shared_id_bigint', explode(',', $meeting->formats))
            ->pluck('key_string')->unique()->all();
        $this->assertNotContains('O', $keys);
        $this->assertContains('WC', $keys);
        $this->assertContains('FIN', $keys);
        $this->assertContains('ENG', $keys);
        $this->assertContains('OLAST', $keys);
        $this->assertContains('OANNIV', $keys);
        $this->assertSame('Lähde: https://www.nasuomi.org/kokous/test-101/', $this->meetingText($meeting, 'comments'));
        $this->assertSame('Tervetuloa.', $this->meetingText($meeting, 'location_info'));
        $second = Meeting::where('source_id', 102)->firstOrFail();
        $secondKeys = Format::whereIn('shared_id_bigint', explode(',', $second->formats))
            ->pluck('key_string')->unique()->all();
        $this->assertContains('MONSPKR', $secondKeys);
        $this->assertContains('NOACCESS', $secondKeys);
        $this->assertSame('Lähde: https://www.nasuomi.org/kokous/test-102/', $this->meetingText($second, 'comments'));
        $this->assertSame('Tervetuloa.', $this->meetingText($second, 'location_info'));
    }

    public function testManualCoordinatesAreBoundToTheCurrentAddress(): void
    {
        $this->initializeMirror();
        File::ensureDirectoryExists($this->stateDirectory);
        $overridePath = $this->stateDirectory . '/overrides.json';
        File::put($overridePath, json_encode(['101' => [
            'address' => 'Testikatu 1, 00100, Helsinki, Suomi',
            'latitude' => 60.1708, 'longitude' => 24.9375,
            'reference' => 'https://example.org/verified-venue',
        ]]));
        config(['nasuomi.coordinate_overrides' => $overridePath]);
        $meeting = $this->sourceMeeting(101, ['karttalinkki' => '']);
        $this->sourceSnapshot([$meeting]);

        $this->assertSame(0, Artisan::call('nasuomi:sync'));
        $this->assertEquals(60.1708, Meeting::firstOrFail()->latitude);

        $this->sourceSnapshot([array_replace($meeting, ['katuosoite' => 'Uusi osoite 2'])]);
        $this->assertNotSame(0, Artisan::call('nasuomi:sync'));
        $this->assertSame(0, Meeting::count());
    }

    public function testResumeDateAndSpanishLocalTimezoneArePreserved(): void
    {
        $this->initializeMirror();
        $today = now('Europe/Helsinki')->format('d.m.Y');
        $tomorrow = now('Europe/Helsinki')->addDay()->format('d.m.Y');
        $this->sourceSnapshot([
            $this->sourceMeeting(101, ['tauolla_pvm_asti' => $today]),
            $this->sourceMeeting(102, ['tauolla_pvm_asti' => $tomorrow]),
            $this->sourceMeeting(103, ['tauolla_pvm_asti' => $today, 'cancelled_for_now' => 'Kyllä']),
            $this->sourceMeeting(104, ['maa' => 'Espanja', 'kaupunki' => 'Fuengirola']),
        ]);

        $this->assertSame(0, Artisan::call('nasuomi:sync'));
        $pausedFormat = (string) Format::where('lang_enum', 'en')->where('key_string', 'PAUSED')->value('shared_id_bigint');
        $this->assertNotContains($pausedFormat, explode(',', Meeting::where('source_id', 101)->value('formats')));
        foreach ([102, 103] as $id) {
            $meeting = Meeting::where('source_id', $id)->firstOrFail();
            $this->assertContains($pausedFormat, explode(',', $meeting->formats));
            $this->assertSame('Lähde: https://www.nasuomi.org/kokous/test-' . $id . '/', $this->meetingText($meeting, 'comments'));
            $this->assertSame('Tervetuloa.', $this->meetingText($meeting, 'location_info'));
        }
        $this->assertSame('Europe/Madrid', Meeting::where('source_id', 104)->value('time_zone'));
        $this->assertSame('18:00:00', Meeting::where('source_id', 104)->value('start_time'));
    }

    public function testDatabaseFailureRollsBackMeetingsFormatsAndAuditChanges(): void
    {
        $this->initializeMirror();
        $this->sourceSnapshot([
            $this->sourceMeeting(101, ['rel_kokousmuodot' => 'Uusi tarkistettava kokousmuoto']),
            $this->sourceMeeting(102),
        ]);
        $formats = Format::count();
        $changes = Change::count();
        $repository = $this->app->make(MeetingRepositoryInterface::class);
        $mock = Mockery::mock($repository);
        $created = 0;
        $mock->shouldReceive('create')->andReturnUsing(function ($values) use ($repository, &$created) {
            if (++$created === 2) {
                throw new RuntimeException('Simulated database write failure');
            }
            return $repository->create($values);
        });
        $this->app->instance(MeetingRepositoryInterface::class, $mock);

        $this->assertNotSame(0, Artisan::call('nasuomi:sync'));
        $this->assertSame(0, Meeting::count());
        $this->assertSame($formats, Format::count());
        $this->assertSame($changes, Change::count());
    }
}
