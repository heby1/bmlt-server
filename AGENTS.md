# Working on the Finnish BMLT mirror

Read this file, `README.md` and `FINNISH_MIGRATION.md` before making changes.
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
- Unknown duration is allowed. Paused meetings remain published with a pause notice.
- Virtual meetings may have NULL coordinates. Physical meetings with unresolved
  coordinates are skipped and reported; never use a map camera position or 0,0.
- Unresolved meetings must not delay importing usable meetings.
- Keep credentials in ignored environment files; never commit passwords or
  package a local environment file in a release.
- Use the Finnish Docker override for local work. Production is SiteGround
  Apache/PHP/MySQL with a cron entry; do not deploy without explicit authorization.

## Documentation and verification

- `FINNISH_MEETING_FLAGS.md` is a curated checklist for human review, including
  issues in extra information. It is separate from runtime reports. The daily
  command must never regenerate it or overwrite review notes.
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
