# Finnish WordPress → BMLT satellite mirror

WordPress remains authoritative. This fork publishes a BMLT-compatible copy of
Finnish NA meeting data; it does not introduce group ownership or replace the
Finnish meeting administration workflow. Read [README.md](README.md),
[AGENTS.md](AGENTS.md), and the separate, manually maintained
[human review list](FINNISH_MEETING_FLAGS.md).

The source is
[`/wp-json/wp/v2/kokoukset`](https://www.nasuomi.org/wp-json/wp/v2/kokoukset).
The 8 October 2026 snapshot has **240 published meetings**. WordPress rejects
`per_page=1000`; the command retrieves all pages with `per_page=100`, checks the
pagination totals and identities, and keeps the complete source snapshot locally.

## Running locally

Use the existing upstream build with the Finnish Docker Compose override. It
starts Apache/PHP and a persistent, initially empty MariaDB database. The web
server binds to `127.0.0.1:8000`; the database is not published on a host port.
Docker Compose **2.24.4 or later** is required for the override syntax.

For a first setup, from the repository root:

```sh
cp .env.example .env
chmod 600 .env
```

Fill `APP_KEY`, `DB_PASSWORD`, `MARIADB_ROOT_PASSWORD` and
`NASUOMI_INITIAL_ADMIN_PASSWORD` with separate values. The initial admin password
must contain at least 12 characters. Keep it available for login. A Laravel
application key can be generated with:

```sh
docker run --rm bmltenabled/bmlt-server-base:8.3 php -r 'echo "base64:" . base64_encode(random_bytes(32)) . PHP_EOL;'
```

The ignored **root `.env`** is outside `src/` and the release artifact. Never
create `src/.env` while building: upstream packaging copies the application tree.
`NASUOMI_STATE_DIR` must be an absolute host path; the example expands `${PWD}`
when Compose runs from the repository root. State is mounted inside the PHP
container at `/var/lib/bmlt-nasuomi`.

Start the server:

```sh
make dev-fi
```

On Linux, the container's Apache user (UID 33) needs write access to
`src/storage` and `src/bootstrap/cache`; use appropriate ownership or an ACL if
the existing host permissions do not allow this. In another terminal, create the
upstream tables, initialize the account, and inspect the proposed import:

```sh
docker compose --env-file .env -p bmlt-fi -f docker/docker-compose.yml -f docker/docker-compose.fi.yml exec -T -w /var/www/html/main_server bmlt php artisan migrate --force
docker compose --env-file .env -p bmlt-fi -f docker/docker-compose.yml -f docker/docker-compose.fi.yml exec -T --user "$(id -u):$(id -g)" -w /var/www/html/main_server bmlt php artisan nasuomi:sync --initialize-only
docker compose --env-file .env -p bmlt-fi -f docker/docker-compose.yml -f docker/docker-compose.fi.yml exec -T --user "$(id -u):$(id -g)" -w /var/www/html/main_server bmlt php artisan nasuomi:sync --dry-run
docker compose --env-file .env -p bmlt-fi -f docker/docker-compose.yml -f docker/docker-compose.fi.yml exec -T --user "$(id -u):$(id -g)" -w /var/www/html/main_server bmlt php artisan nasuomi:sync
```

Open `http://127.0.0.1:8000/main_server/` and log in with `NASUOMI_ADMIN_USERNAME` (default
`serveradmin`) and the configured initial password. The scheduler is disabled
locally by default. `make down-fi` stops the stack and retains its database and
state. Repeated imports update the same WordPress-owned records.

Using the host UID/GID keeps private sync files readable by the local account.
Run database tests in a separate disposable database, such as `rootserver_test`,
granting the same Docker database user access first. Laravel's `RefreshDatabase`
resets the whole selected database; a different table prefix alone is insufficient.
With that test database prepared:

```sh
docker compose --env-file .env -p bmlt-fi -f docker/docker-compose.yml -f docker/docker-compose.fi.yml exec -T -e APP_ENV=testing -e DB_PREFIX= -e DB_DATABASE=rootserver_test -w /var/www/html/main_server bmlt vendor/bin/phpunit tests/Feature/NasuomiSyncTest.php
```

## Mapping and compatibility decisions

The PHP Laravel command uses existing BMLT models/repositories and native custom
formats. It adds no tables, schema columns, geocoding provider or dependencies.
The target remains disposable, but changes are limited to source-owned meetings.

| WordPress concept | BMLT representation / decision |
| --- | --- |
| Unique post ID | Native `source_id`; each post is a distinct meeting, including repeated names. No fabricated group record. |
| Ownership | One **Suomen alue** area service body. Imported meetings have no associated BMLT root server. Other/manual meetings are outside sync ownership. |
| Finnish geographical area | Preserve Etelä/Keski/Länsi/Itä/Pohjoinen/Internet/Ulkomaat in source notes. Do not invent service bodies for these browsing categories. |
| Title | Decode HTML entities and retain Finnish characters/emoji. |
| Weekday/start | Finnish weekday names become native weekdays; accept both `18.00` and `18:00`. |
| Duration | Minutes become native duration. `0` means unspecified and is stored as NULL; it is not automatically an Open-Ended format. |
| Location | Existing street/postcode/city/country fields. Blank domestic country defaults to Finland; missing/malformed source postcodes remain reported, not guessed. |
| Language | Exact native Finnish/English/Persian language formats; exact custom Swedish/Russian formats when needed. Do not substitute Lithuanian/Russian for Russian. |
| Equivalent formats | A&P → **St + Tr**, Askel → St, Teema → To, Meditaatio → ME, Perinnekokous → Tr, Esteetön pääsy → WC; closed literature combines C + BK. A&P does not mean the native IW book format. |
| Non-equivalent source labels | Preserve their meaning as native custom Finnish formats, including conditional openness, negative accessibility, written step work and treatment-facility venues. Unknown labels remain visible and reported. |
| Relationship strings | Rendered text, not relationship IDs. A comma can belong inside a label. Two different source definitions render as Tarvittaessa avoin. An empty format relationship remains unspecified; it does not imply closed attendance. |
| Extra information | Preserve normalized readable Finnish/English notes, links and emoji using existing normal/long text storage; retain the original HTML in source snapshots. Do not infer attendance or calendar policies from prose. |
| Pause | Publish with the native custom **Tauolla** format and a notice. `Kyllä` means indefinite pause; a future resume date pauses the meeting before that date, resuming on the specified date. Expired dates alone do not pause a meeting. |
| Internet meeting | Native virtual venue, joining URL, no invented physical location or coordinates. |
| Physical coordinates | Accept explicit target coordinates in source/map redirect URLs, a matching cache entry, or an address-bound manual override. Reject map camera positions and 0,0. |
| Unresolved physical meeting | Skip and report it; if an owned previously imported meeting becomes invalid/unresolved, remove that owned copy after a complete successful fetch. Usable meetings continue importing. |
| Timezone | Europe/Helsinki for Finnish/Internet meetings. The Spain meeting provisionally uses Europe/Madrid and is flagged for organizer confirmation. |
| Removal/unpublishing | A post absent from a complete published source snapshot removes its owned BMLT copy. Fetch failure or inconsistent pagination makes no meeting changes. |

Weekday numbering differs between storage and the public legacy API:

| Source weekday | Native database | Legacy API |
| --- | ---: | ---: |
| Sunnuntai | 0 | 1 |
| Maanantai | 1 | 2 |
| Tiistai | 2 | 3 |
| Keskiviikko | 3 | 4 |
| Torstai | 4 | 5 |
| Perjantai | 5 | 6 |
| Lauantai | 6 | 7 |

The added formats use existing BMLT format records and Finnish/English metadata:

| Source label | Native custom key |
| --- | --- |
| Avoin kuun ensimmäinen | OFIRST |
| Avoin kuun viimeinen | OLAST |
| Avoin vuosipäivinä | OANNIV |
| Kuukauden toinen viikko avoin | OSECOND |
| Tarvittaessa avoin | OREQUEST |
| Askeltyökokous | STEPWORK |
| Ei pääsyä pyörätuolilla | NOWHEEL |
| Esteellinen | OBSTRUCT |
| Tila ei ole esteetön | NOACCESS |
| Laitos | INST |
| Ryhmässä kerran kuussa alustaja, joka jakaa kokemustaan n.15min | MONSPKR |
| ruotsi / venäjä | SWE / RUS (language formats) |
| Derived pause notice | PAUSED (English metadata), TAUOLLA (Finnish metadata) |

These keys preserve source labels without making them equivalent to a broader
upstream format. The ambiguous Esteellinen definition remains a human review
item. Newly encountered labels receive a deterministic custom key and appear in
the run report; raw format/language strings also remain in meeting comments.

The stock BMLT model describes **weekly recurring meetings**. Notes currently
contain fortnightly/monthly attendance, one-off venue/time changes, phase-based
meeting starts, variable durations and conditional language/openness. These are
preserved and documented for human review; this fork does not add an exception
calendar, new fields or automatic prose interpretation.

Compatibility limits are visible rather than hidden:

- The legacy search API substitutes its default duration when native duration is
  NULL. Consumers can therefore see a conventional duration for an unspecified
  source duration.
- TSML export does not carry every custom Finnish format. Consumers of that
  export must also consult notes; exact custom meanings are available through
  native BMLT format/search interfaces.
- Virtual NULL coordinates are valid. Some existing admin/location editing
  workflows assume coordinates; importing virtual records is supported, but
  using those editors may require separate upstream work.
- The stock admin API requires a postcode, or both city and province. Finnish
  source records without a postcode have a city but no province; they can be
  imported through native repositories, but an unchanged admin API edit can fail
  its address validation.
- A map link can point at an old venue even when its coordinates parse. The
  curated review list records confirmed mismatches and location clarifications.

Investigation of the WordPress placeholder/count bug remains later work.
The three empty source placeholders disappeared before
the refreshed 240-meeting snapshot; their history stays in the human review list.

## Finnish localization

Finnish is available in the language selector. This fork's root `.env.example`
sets the native BMLT `LANGUAGE=fi` setting; use the same setting in the local or
production environment to make Finnish the default. Upstream's fallback remains
English. A previously selected browser language, stored as `bmltLanguage`, takes
precedence; choose **Suomi** to change it.

Finnish views use a 24-hour clock and Monday-first weekday choices and meeting
sorting. Stored weekdays remain Sunday = 0 through Saturday = 6, preserving API
and import compatibility. Dates and timestamps use Finnish presentation.

All **303** keys and English texts in the supplied `translations.xlsx` exactly
matched `src/resources/js/lang/en.ts`. The workbook now retains columns A (key)
and B (English), and adds C (**Suomi**) matching `fi.ts`. This also preserves the
existing spreadsheet import convention. Runtime translations come from the code;
editing the workbook alone does not change the application.

The workbook does not include the **36 Yup validation messages**, translated in
`fi.ts`, or the **178 backend strings** in nine `src/lang/fi` files copied from
English. It also does not contain database format names/descriptions. Imported
custom formats already have Finnish metadata. Stock formats without Finnish
metadata remain visible using their English metadata; translating that catalog
is separate from the UI workbook. The legacy `GetSearchResults` endpoint already
falls back to English format metadata. `GetFormats` filters by the requested
language; clients needing the complete stock catalog should explicitly request
`lang_enum=en` until that catalog has Finnish translations.

### Changes needed for fuller compatibility

The following are documented choices for later approval, not schema changes made
by this migration. Prefer preserving Finnish meeting meanings in BMLT. Source
corrections are appropriate when the source itself is wrong or incomplete.

| Gap | BMLT or consumer change | Finnish source correction / alternative |
| --- | --- | --- |
| Nonweekly meetings and one-off exceptions | Add structured recurrence/exception support and teach clients to honor it. Weekly rows and notes alone cannot provide an accurate calendar. | Organizers clarify actual recurrence and dated exceptions; do not recast a fortnightly/monthly meeting as weekly. |
| Conditional openness and language | Clients interpret the custom meanings; future structured conditions could support filtering by the actual occurrence. | Align structured source labels with verified notes, including alternating openness and monthly language exceptions. |
| Unspecified or variable ending | Permit NULL duration in admin writes and preserve it in public export instead of substituting a default. | Set a duration only when organizers confirm an actual fixed ending; otherwise retain unspecified duration. |
| Long meaningful notes | Raise admin API/editor limits to match existing long-text storage. | Remove genuinely obsolete or duplicated notices after review; do not truncate relevant text to satisfy BMLT limits. |
| Finnish addresses without province/postcode | Accept verified street/city/coordinates without requiring a fabricated province. | Supply verified missing postcodes and correct malformed six-digit values. |
| Unresolved or stale map links | Continue reviewed coordinate overrides; any future geocoding provider needs a separate choice. | Correct stale links or expose verified point coordinates in WordPress after approving that source schema change. |
| Pauses | Clients honor Tauolla; a future pause-until field would support accurate calendar filtering and automatic resumption. | Clarify far-future dates and indefinite flags; keep structured pause data current. |
| Attendance/accessibility nuances | Use exact custom descriptions rather than broad women-only, men-only, child-friendly or fully accessible assumptions. | Organizers confirm actual audience, age limits, entrance and toilet accessibility separately. |
| Rendered relationship labels | Keep explicit mappings; stable relationship identifiers would remove ambiguous comma/join parsing. | Expose relationship IDs with labels through REST if that API change is later approved. |

The separate manual checklist records the affected meetings and the decisions
required; these proposals do not authorize changes to WordPress or BMLT's schema.

## State, coordinate overrides and daily reports

`NASUOMI_STATE_DIR` holds source snapshots, per-run JSON reports and the coordinate
cache outside the application/public tree. Snapshots and reports retain 30 days;
coordinate results persist while their source location matches. New or changed
locations are resolved once and cached, instead of following map redirects daily.
Failures and source discrepancies remain recorded on each run.

| Environment variable | Purpose / default |
| --- | --- |
| `NASUOMI_SOURCE_URL` | Published WordPress meeting endpoint. |
| `NASUOMI_STATE_DIR` | Absolute writable private directory; local host example `var/nasuomi`, container `/var/lib/bmlt-nasuomi`. |
| `NASUOMI_COORDINATE_OVERRIDES` | Optional absolute path to a private JSON file containing verified WP-ID/address-bound coordinates. |
| `NASUOMI_ADMIN_USERNAME` | Initial admin account, default `serveradmin`. |
| `NASUOMI_INITIAL_ADMIN_PASSWORD` | Required explicit bootstrap password; do not use the upstream example password. |
| `NASUOMI_SYNC_TIME` | Local Finnish schedule time, default `04:15`. |
| `NASUOMI_SCHEDULE_ENABLED` | Scheduler opt-in, default `false`. |

State files are `snapshots/<timestamp>-source.json`,
`reports/<timestamp>-report.json`, and `coordinates.json`. A dry run retrieves and
validates source data and describes changes without changing the database; an
initialization-only run sets up the service body and formats and replaces the
upstream default admin password without fetching meetings. It does not create
the upstream tables: run `migrate --force` first. Normal imports never reset an
existing admin password. `--source-file` is for a trusted, complete offline test fixture,
not a shortcut around production completeness checks.

Exit status **0** means all records were usable (or initialization completed),
**2** means some records were skipped (usable records are committed on a normal run), and
**1** means the run failed. Fetch or database-transaction failures leave meetings
unchanged. A dry run can also return 2.
An unresolved lookup is cached for six hours; subsequent runs retry it. Successful
coordinates have no expiry while WP ID, normalized address and map URL match.

Manual coordinate overrides are keyed by WP ID and carry an `address`,
`latitude` and `longitude`. The address must exactly match the current normalized
source address, so a venue move cannot silently reuse an old point. Normalization
joins nonempty street, postcode, city and country with `, `; blank country becomes
`Suomi`. Use the address from the run report/cache when preparing an override.
For a fictitious meeting, the JSON shape is:

```json
{
  "123": {
    "address": "Testikatu 1, 00100, Helsinki, Suomi",
    "latitude": 60.17,
    "longitude": 24.94,
    "reference": "Verified venue entrance; replace this example with real evidence"
  }
}
```

`reference` is an optional human note. Keep the file in
the private state directory and set `NASUOMI_COORDINATE_OVERRIDES` to its path.
No Google API key or billing account is required for this workflow. An alternative
geocoding provider would need a separate informed choice.

[FINNISH_MEETING_FLAGS.md](FINNISH_MEETING_FLAGS.md) is the curated **human**
checklist. Updating source information or resolving a human question does not
authorize a daily job to rewrite that document or its review notes.

## SiteGround deployment and scheduling

Production needs Apache, PHP **8.3+** with the modules listed in README, a MySQL
database, and PHP CLI for cron. A bare database does not publish BMLT search APIs.
Use the existing SiteGround PHP/MySQL hosting; Docker is for local development.

The raw fork is source code. Build **this fork** in a separate build checkout, or
remove existing generated build artifacts before using the upstream flow. An
existing ZIP can be stale after a PHP-only change:

```sh
make clean
CI=1 CONTAINER=1 make zip
```

`make clean` removes generated dependencies/assets and the ZIP; it does not remove
the root runtime `.env` or private sync state. Before building, ensure `src/.env`
is absent and all secrets/state live outside
`src/`. Inspect `build/bmlt-server.zip` for environment/state files before upload.
Upload that ZIP and extract it on the server as `public_html/main_server`, as in
[upstream installation instructions](installation/README.md). Bundled Composer
dependencies and compiled front-end assets are needed; uploading an unbuilt Git
checkout is insufficient.

Create production runtime configuration **after** building/uploading, using
injected private environment variables if the host supports them or the normal
Laravel `main_server/.env`. Configure production DB host/database/user/password,
`DB_PREFIX`, a persistent `APP_KEY`, `APP_DEBUG=false`, `LANGUAGE=fi`, the bootstrap admin
password, and an absolute `NASUOMI_STATE_DIR` **outside `public_html`**. Existing
environment-backed auto-config handling is retained; do not maintain conflicting
DB values in both environment and a legacy `auto-config.inc.php`.

Keep the runtime environment readable only by the hosting account. Existing
Apache rewrites protect application files; verify that a request to
`/main_server/.env` returns 403/404 before exposing the configured deployment.
The account needs write access to Laravel `storage`, `bootstrap/cache` and the
private state directory. Preserve production environment/state when replacing
the release directory.

Using the host's PHP 8.3+ CLI binary, in `main_server`, run `php artisan migrate
--force`, then initialization, dry run and a manual first sync as shown above
without the Docker prefix. Confirm the
public BMLT API and admin login before enabling scheduled imports. No production
deployment or cron installation is performed by local setup.

Set:

```dotenv
NASUOMI_SCHEDULE_ENABLED=true
NASUOMI_SYNC_TIME=04:15
```

For a cron service operating in **UTC**, use the two checks required by Finnish
daylight saving time, replacing paths with the SiteGround account's real paths:

```cron
15 1,2 * * * cd /absolute/path/public_html/main_server && /absolute/path/to/php83 artisan schedule:run >> /absolute/private/nasuomi/cron.log 2>&1
```

Laravel schedules the command in `Europe/Helsinki`: 01:15 UTC is 04:15 in summer,
02:15 UTC is 04:15 in winter, and the other check is not due. This yields one
scheduled import daily and avoids running the scheduler every minute. Verify the
hosting cron timezone and PHP binary rather than assuming their names. Existing
scheduler overlap protection and the sync lock prevent concurrent imports.

At the investigation date, the latest official
[4.2.8 release](https://github.com/bmlt-enabled/bmlt-server/releases/tag/4.2.8)
was published on 25 September 2026. This checkout starts at
`85fc761fa1659a3cb57507c0fad081e0edc99bdc`,
[six commits ahead of 4.2.8](https://github.com/bmlt-enabled/bmlt-server/compare/4.2.8...85fc761fa1659a3cb57507c0fad081e0edc99bdc),
with zero commits behind. The release ZIP is a built artifact, while the fork
contains source and those later changes. Downloading the stock 4.2.8 ZIP alone
would omit this fork's sync command; build this checkout for deployment.
