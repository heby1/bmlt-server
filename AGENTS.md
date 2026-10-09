# Working on the Finnish BMLT mirror

Read this file, `README.md` and `FINNISH_MIGRATION.md` before making changes.
For production handoff, read `SITEGROUND_DEPLOYMENT.md`.
Respect the user's goal. Keep changes concise; do not refactor working code or
add dependencies, abstractions or infrastructure without an explicit need.

## Purpose and ownership

- This fork is a satellite mirror of Finnish NA meetings, not a full BMLT adoption.
- WordPress is authoritative: `https://www.nasuomi.org/wp-json/wp/v2/kokoukset`.
- Each WordPress post is one meeting. Matching names do not establish duplicates.
- The mirror owns only meetings identified by WordPress `source_id` in the
  **Suomen alue** service body, with no BMLT root server association.
- The target database is disposable. Sync may remove vanished or invalid owned
  entries after fetching a complete, consistent source snapshot.

## Implementation boundaries

- Use PHP, existing Laravel commands and BMLT repositories. No new database
  tables or third-party dependencies are needed.
- Preserve Finnish meanings with existing fields, notes and native custom formats.
  Do not guess ambiguous attendance, accessibility or recurrence policies.
- `comments` (Kommentit) contains only `Lähde: <WordPress permalink>`.
  `location_info` (Lisätiedot) contains only readable plain text from WordPress
  `lisatiedot`. Turn paragraph, `<br>` and block boundaries into spaces and
  collapse whitespace so single-line editors preserve readable word boundaries.
  Preserve source note content, link URLs and emoji. Do not append generated
  English, pause, venue, source, area, format or language metadata to Lisätiedot.
- Represent format/language relationships with native formats. Keep
  `lisatiedot_en` in the raw source snapshot without appending it to BMLT text.
  Do not copy WordPress Alue into text fields; retain the Internet-area check for
  virtual venue classification.
- Unknown duration is allowed. Paused meetings remain published with the native
  Tauolla format and a runtime warning; source note text stays as provided.
- A normal sync replaces the owned meetings' two text fields, clearing previous
  generated metadata in place without changing database structure.
- The source map link is the best available location evidence. The user confirmed
  that a nearby map pin may be more useful than the postal address, including
  Messukatu 4 versus Lutakonaukio 3 in Jyväskylä. A different nearby address label
  alone is not a source error; large actual point discrepancies need human review.
- Resolve the actual linked place with GET redirects, explicit place/route-target
  coordinates or the identified target record in Google's embedded-map JSON.
  Prefer CID-only requests when a place ID exists so a text query cannot replace
  the pin. Never use viewport coordinates, unrelated HTML points or 0,0. No API
  keys, separate geocoding service or new dependencies are required.
- Virtual meetings may have NULL coordinates. Physical meetings with unresolved
  coordinates are skipped and reported. Google's page structure is undocumented;
  retain failure reports and verified manual overrides if extraction stops working.
- Keep matching successful coordinates indefinitely. Resolver version 2 retries
  older negative cache entries immediately; current negative results expire after
  six hours. Keep coordinate state outside public/build artifacts.
- Unresolved meetings must not delay importing usable meetings.
- Keep credentials in ignored environment files; never commit passwords or
  package a local environment file in a release.
- Use the Finnish Docker override for local work. Production is SiteGround
  Apache/PHP/MySQL with a cron entry; do not deploy without explicit authorization.
- Production uses `https://www.nasuomi.org/bmltfi/`; extract the upstream ZIP's
  `main_server` directory and rename it to `bmltfi`. Keep local `/main_server/`
  development URLs unchanged.
- Failure/partial-import email is opt-in through `NASUOMI_ALERT_EMAIL` and existing
  Laravel mail settings. Keep recipients and mail credentials private. Dry runs,
  initialization and successful imports do not send mail; do not send test mail
  without an explicit task instruction.

## Documentation and verification

- `FINNISH_MEETING_FLAGS.md` is a curated checklist for human review, including
  issues in extra information. It is separate from runtime reports. The daily
  command must never regenerate it or overwrite review notes.
- Track `AGENTS.md`, `FINNISH_MIGRATION.md`, `FINNISH_MEETING_FLAGS.md` and
  `SITEGROUND_DEPLOYMENT.md` in this fork; keep root operational docs outside the
  upstream runtime ZIP. Keep the human checklist limited to public meeting data
  and review notes, with no private contacts. Credentials, runtime state/reports
  and generated build outputs remain ignored. Future upstream translation PRs
  may omit fork-specific operational docs when irrelevant; no repository move is
  required.
- Record compatibility gaps and required policy decisions in
  `FINNISH_MIGRATION.md`; do not silently change BMLT or source meanings.
- Check dry-run behavior, initial import, unchanged rerun, source changes and
  deletions, invalid records, and fetch failure with no database changes.
- Preserve upstream conventions and build packaging. Keep Finnish frontend and
  `src/lang/fi` translations aligned with English keys and placeholders. Finnish
  views use a 24-hour clock and Monday first; native weekday IDs stay unchanged.
- `translations.xlsx` contains the 303 frontend UI strings (English and Finnish),
  not Yup messages, PHP translations or database format metadata. Update its
  Finnish column when changing the corresponding frontend translations.
- Investigation of the WordPress placeholder/count bug is later work.
