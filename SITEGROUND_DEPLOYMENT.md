# Deploying the Finnish mirror on SiteGround

Production URL: **https://www.nasuomi.org/bmltfi/**. This is a first-install
handoff for the built fork, using SiteGround PHP/MySQL. The database starts empty;
migrations, initialization and the WordPress REST sync create its contents. No
local MySQL dump is needed. The verified source currently has 240 meetings.

Use `bmlt-server.zip` from the supplied SiteGround handoff (prepared locally at
`build/siteground/bmlt-server.zip`) and the configuration template
[deployment/siteground.env.example](deployment/siteground.env.example), or the
private `private/server.env.example` copy supplied alongside the ZIP. Extract
the handoff on your own machine and upload only the server ZIP to `public_html`;
keep its configuration and private cache outside the public directory. For build commands, mapping
decisions and runtime behavior, see [FINNISH_MIGRATION.md](FINNISH_MIGRATION.md).
Replace every filesystem/PHP placeholder below with the host's verified path.

## 1. Prepare access and PHP

In **Site Tools → Devs → SSH Keys Manager**, import your public SSH key and use
the displayed hostname, username and port. SiteGround uses key authentication
and port 18765. [Official SSH instructions](https://www.siteground.com/kb/what-is-ssh).

Set the website's PHP version to **8.3 or newer** in **Devs → PHP Manager**.
Verify the web runtime separately from CLI; changing the website setting alone
does not establish which binary cron will run.
[Official PHP Manager instructions](https://www.siteground.com/kb/what-is-managed-php).

Over SSH, identify the intended CLI binary and verify it:

```sh
/absolute/path/to/php83 -v
/absolute/path/to/php83 -m
```

Confirm CLI and web PHP have `curl`, `gd`, `intl`, `mbstring`, `pdo_mysql`, `dom`,
`xml` and `zip`, as required by [README.md](README.md). HTTPS/SMTP also need
working TLS support. Use this same verified CLI binary for every command and cron.

## 2. Create an empty database and its user

Open **Site Tools → Site → MySQL**. Create a database in **Databases**, create a
dedicated user in **Users**, and assign that user to this database through
**Manage Users → Add New Database**. Grant all privileges **on this one database**
so migrations can create/alter tables and sync can maintain records. Record the
generated database/user names, password and host privately.
[Official database/user instructions](https://www.siteground.com/tutorials/php-mysql/create-user-database).

Use these values for `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` and
`DB_PASSWORD`. The template uses `localhost:3306`; confirm the host's connection
settings. Keep `DB_PREFIX=na` consistent after installation. Give this application
its own empty database; do not use the WordPress database.

## 3. Upload the built server and configure it

For a first install with a fresh target directory:

```sh
cd /absolute/path/public_html
unzip /absolute/upload/path/bmlt-server.zip
mv main_server bmltfi
```

The upstream ZIP contains `main_server`; renaming that directory gives the
approved `/bmltfi/` URL. Keep its bundled dependencies, compiled assets and
`.htaccess` files. A bare Git checkout is not the deployment package.

Create a private state directory **outside `public_html`**:

```sh
mkdir -p /absolute/private/nasuomi-state
chmod 700 /absolute/private/nasuomi-state
```

After upload, copy the environment template to `public_html/bmltfi/.env`, edit it
on the server and set permissions to `600`. Fill the DB credentials, a new
bootstrap admin password of at least 12 characters, and the absolute private
`NASUOMI_STATE_DIR`. Keep these settings:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.nasuomi.org/bmltfi
ASSET_URL=https://www.nasuomi.org/bmltfi
LANGUAGE=fi
NASUOMI_ADMIN_USERNAME=serveradmin
NASUOMI_SYNC_TIME=04:15
NASUOMI_SCHEDULE_ENABLED=false
```

Generate a production `APP_KEY` once, place it in `.env`, and retain it for updates:

```sh
/absolute/path/to/php83 -r 'echo "base64:" . base64_encode(random_bytes(32)) . PHP_EOL;'
```

Ensure the hosting account's web PHP and CLI can write `bmltfi/storage`,
`bmltfi/bootstrap/cache` and the private state directory. Use account ownership
and appropriate permissions. Configure DB settings through the environment;
avoid conflicting values in a legacy `auto-config.inc.php`.

The handoff also contains `private/coordinates.json`, a seed of verified
coordinates matching current source locations. Copy it to the private state
directory before the first sync to avoid repeating already verified map lookups:

```sh
cp /absolute/handoff/path/private/coordinates.json /absolute/private/nasuomi-state/coordinates.json
chmod 600 /absolute/private/nasuomi-state/coordinates.json
```

Keep this cache outside `public_html`. New or changed source locations are still
resolved automatically. The cache is optional; it contains no database dump or
application credentials.

Check that `https://www.nasuomi.org/bmltfi/.env` returns **403 or 404**. Preserve
the production `.env`, application key and private state when updating the ZIP.

## 4. Create tables and run the first import

From the installed application directory:

```sh
cd /absolute/path/public_html/bmltfi
/absolute/path/to/php83 artisan config:clear
/absolute/path/to/php83 artisan migrate --force
/absolute/path/to/php83 artisan nasuomi:sync --initialize-only
/absolute/path/to/php83 artisan nasuomi:sync --dry-run
/absolute/path/to/php83 artisan nasuomi:sync
```

Initialization creates the Finnish service body/formats and sets the initial
admin password. Normal sync preserves that password. Review the first report in
the private state directory: a complete import returns **0**; **2** means skipped
meetings with usable records imported; **1** means failure. The current source
should import all 240, including 18 virtual meetings with NULL coordinates.

Log in at the production URL using the configured admin credentials. Verify the
public feed and a few meeting times, notes and map pins:

```text
https://www.nasuomi.org/bmltfi/client_interface/json/?switcher=GetSearchResults&get_used_formats=1&lang_enum=fi
```

Run sync again and confirm an unchanged result before scheduling it.

## 5. Optional email for failed or partial imports

Set `NASUOMI_ALERT_EMAIL` privately to the agreed recipient. Blank disables
alerts. The private handoff copy may contain that recipient; keep it out of Git.
Failed or partial **normal** syncs send one plain email using Laravel's existing
mail configuration. Successful syncs, dry runs and initialization do not send.

Use `MAIL_MAILER=sendmail` only after confirming that the configured
`MAIL_SENDMAIL_PATH` exists and works on this account. Otherwise use an existing
SiteGround mailbox's SMTP settings from **Email → Accounts → Actions → Mail
Configuration → Manual Settings**. Use its exact outgoing server, full mailbox
address as username and mailbox password. SiteGround documents port **465**;
use `MAIL_ENCRYPTION=ssl` with it.
[Official mailbox settings](https://www.siteground.com/kb/how_to_configure_my_mail_client).

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=REPLACE_WITH_OUTGOING_SERVER
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=REPLACE_WITH_EXISTING_MAILBOX
MAIL_PASSWORD="REPLACE_WITH_MAILBOX_PASSWORD"
MAIL_FROM_ADDRESS=REPLACE_WITH_EXISTING_MAILBOX
MAIL_FROM_NAME="NA Suomi meeting mirror"
```

After configuring mail, clear cached configuration and verify delivery with an
intentional test before relying on alerts. This command sends a test to the
privately configured recipient:

```sh
/absolute/path/to/php83 artisan config:clear
/absolute/path/to/php83 artisan tinker --execute='\Illuminate\Support\Facades\Mail::raw("BMLT deployment mail test", function ($message) { $message->to(config("nasuomi.alert_email"))->subject("BMLT mail test"); });'
```

Alerts include a short summary, skipped IDs/reasons and the private report path;
they exclude credentials and raw source snapshots. Delivery failure prints a
warning and preserves the sync result. PHP/cron failures before Laravel starts
are outside application alerts; inspect `cron.log`. Host SMTP/sendmail delivery
has not been verified or used by this local handoff.

## 6. Enable the daily schedule

Set `NASUOMI_SCHEDULE_ENABLED=true`, retain `NASUOMI_SYNC_TIME=04:15`, and clear
cached configuration. In **Site Tools → Devs → Cron Jobs**, add the following
schedule, using your verified absolute paths.
[Official cron setup](https://www.siteground.com/kb/manage-cron-jobs).

```cron
15 1,2 * * * cd /absolute/path/public_html/bmltfi && /absolute/path/to/php83 artisan schedule:run >> /absolute/private/nasuomi-state/cron.log 2>&1
```

SiteGround's server cron uses UTC. Laravel checks the configured
`Europe/Helsinki` schedule: 01:15 UTC is 04:15 in summer, 02:15 UTC is 04:15 in
winter, and the other check is not due. Verify the first scheduled report.
[SiteGround's timezone explanation](https://www.siteground.com/blog/what-is-cron-what-use).

The redirection keeps cron output in the private log. SiteGround's own cron
email sends command output only when output remains unredirected; it is separate
from application failure alerts.
[Official cron email behavior](https://www.siteground.com/kb/manage-cron-jobs).

## Repository and release files

Track `AGENTS.md`, `FINNISH_MIGRATION.md`, `FINNISH_MEETING_FLAGS.md` and this guide
in this fork. Root operational documents stay outside the upstream runtime ZIP;
include the guide alongside the ZIP in an operator handoff. The curated checklist
contains versioned public meeting information and human review notes, without
private contact information. Daily sync never rewrites it.

Keep completed environment files, recipients/credentials, state, runtime reports
and generated build outputs ignored. A later upstream translation PR can omit
fork-specific operational documents unless relevant to that change. No repository
move or restructuring is needed.

## Prepared package verification

On 9 October 2026, the production ZIP was tested locally on PHP 8.3 with a
separate empty database and the `/bmltfi/` directory name. Migrations,
initialization and import succeeded: **240 meetings, zero skips**, followed by
**240 unchanged** on a second sync. The public feed returned 222 physical/hybrid
locations and 18 virtual meetings; the page and compiled assets returned 200,
and `.env` returned 403. All **24 migration feature tests / 219 assertions** pass.
Production mailbox delivery still needs the account-specific test described
above; no production deployment or real email was performed here.
