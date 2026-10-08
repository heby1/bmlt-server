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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Mockery;
use RuntimeException;
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

    private function comments(Meeting $meeting): string
    {
        return $meeting->data()->where('key', 'comments')->value('data_string')
            ?? $meeting->longdata()->where('key', 'comments')->value('data_blob')
            ?? '';
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
        $this->assertStringContainsString('https://example.org/info', $this->comments($first));
        $this->assertStringContainsString('🔥', $this->comments($first));
        $this->assertStringContainsString('TAUOLLA', $this->comments($second));
        $this->assertStringNotContainsString('<p>', $this->comments($first));
        $changes = Change::count();
        $ids = Meeting::orderBy('source_id')->pluck('id_bigint')->all();

        $this->assertSame(0, Artisan::call('nasuomi:sync'));
        $this->assertSame($ids, Meeting::orderBy('source_id')->pluck('id_bigint')->all());
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

    public function testSourceLabelsPreserveConditionalOpennessAndCommaInsideLabel(): void
    {
        $this->initializeMirror();
        $this->sourceSnapshot([$this->sourceMeeting(101, [
            'rel_kokousmuodot' => 'Avoin kuun viimeinen, Avoin vuosipäivinä, ja Esteetön pääsy',
            'rel_kokouskielet' => 'englanti ja suomi',
        ]), $this->sourceMeeting(102, [
            'rel_kokousmuodot' => 'Ryhmässä kerran kuussa alustaja, joka jakaa kokemustaan n.15min',
        ])]);

        $this->assertSame(0, Artisan::call('nasuomi:sync'));

        $meeting = Meeting::where('source_id', 101)->firstOrFail();
        $keys = Format::whereIn('shared_id_bigint', explode(',', $meeting->formats))
            ->pluck('key_string')->unique()->all();
        $this->assertNotContains('O', $keys);
        $this->assertContains('WC', $keys);
        $this->assertContains('FIN', $keys);
        $this->assertContains('ENG', $keys);
        $this->assertStringContainsString('Avoin kuun viimeinen', $this->comments($meeting));
        $second = Meeting::where('source_id', 102)->firstOrFail();
        $this->assertStringContainsString('Ryhmässä kerran kuussa alustaja,', $this->comments($second));
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
        $this->assertStringNotContainsString('TAUOLLA', $this->comments(Meeting::where('source_id', 101)->firstOrFail()));
        $this->assertStringContainsString('TAUOLLA', $this->comments(Meeting::where('source_id', 102)->firstOrFail()));
        $this->assertStringContainsString('TAUOLLA', $this->comments(Meeting::where('source_id', 103)->firstOrFail()));
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
