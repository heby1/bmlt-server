# SiteGround deployment in three steps

Production URL: **https://www.nasuomi.org/bmltfi/**.
Detailed setup and maintenance: [FINNISH_MIGRATION.md](FINNISH_MIGRATION.md).

## 1. Deploy and configure the server

The server was deployed through the authorized SSH alias `nasuomi` to
`/home/customer/www/nasuomi.org/public_html/bmltfi`.
PHP **8.3.35** is configured for this folder; WordPress's PHP setting is unchanged.
The empty database was migrated and initialized, then populated through the REST
sync. No database dump was needed.

The private state directory is `/home/customer/nasuomi-state`, outside
the public directory. Keep production `.env`, application key, credentials,
alert recipient and state private; preserve them during updates. The tracked
[environment template](deployment/siteground.env.example) contains placeholders.

Production verified on **9 October 2026**: **240 meetings, zero skips**, then
**240 unchanged**. HTTPS admin login, UI/assets and public API passed; `.env`
returns **403**. State is private, outside the public directory.

## 2. Add cron yourself in SiteGround

After deployment is confirmed, open **Site Tools → Devs → Cron Jobs**.
The user adds this job in the dashboard; the deployment task does not write
crontab. [SiteGround cron instructions](https://www.siteground.com/kb/manage-cron-jobs).

Schedule (minute 15, hours 1 and 2, every day):

```cron
15 1,2 * * *
```

Command using the verified PHP 8.3.35 CLI:

```sh
cd /home/customer/www/nasuomi.org/public_html/bmltfi && /usr/local/php83/bin/php-cli artisan schedule:run >> /home/customer/nasuomi-state/cron.log 2>&1
```

Production configuration uses `NASUOMI_SCHEDULE_ENABLED=true` and
`NASUOMI_SYNC_TIME=04:15` after the first import passes. SiteGround cron uses UTC;
Laravel runs once at **04:15 Europe/Helsinki**, choosing the appropriate winter
or summer check. [SiteGround timezone explanation](https://www.siteground.com/blog/what-is-cron-what-use).

## 3. Check the result and alerts

Log in at the production URL with the privately configured admin credentials.
Check the [meeting feed](https://www.nasuomi.org/bmltfi/client_interface/json/?switcher=GetSearchResults&get_used_formats=1&lang_enum=fi)
and the first scheduled report in the private state directory.

`NASUOMI_ALERT_EMAIL` enables mail for failed or partial normal imports.
Successful runs, previews and initialization stay silent. Configured transport:
`MAIL_SENDMAIL_PATH="/bin/sendmail -t -i"`; the alert recipient remains private.
**No email was sent; delivery is untested.** PHP/cron startup failures appear in `cron.log`.
