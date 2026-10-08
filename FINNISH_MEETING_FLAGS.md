# Finnish meetings: curated human review list

Reviewed against the public WordPress API on **8 October 2026**, after the source count changed from 243 to **240**. This is a manually maintained review document for source corrections, organizer questions and mapping choices. The migration command and daily scheduler must never generate, refresh or overwrite this file. Runtime JSON reports are separate.

Every current source meeting has a weekday, start time, language, street, city and map/joining URL. No identical name/weekday/start/city schedules were found; repeated group names on different days are valid distinct meetings. No source edits have been made by this project.

**Status conventions:** Open = needs a human answer; Policy review = source information is meaningful but its structured representation needs a choice; Accepted = agreed behavior, not bad data; Resolved = historical issue absent from the current source. Add reviewer, date and resolution beside the status when a decision is made. A source correction and a migration policy choice are different actions.

## Source address fields

These fields need source maintenance. Meetings remain valid when their street and city are usable; the importer must not invent a postcode.

| Meeting | Regular schedule / city | Evidence | Action | Status |
| --- | --- | --- | --- | --- |
| [NA Hämis (7820)](https://www.nasuomi.org/kokous/na-hamis-2/) | Torstai 17:30 · Turku | Empty `postinumero` | Source correction: supply the verified postcode. | Open |
| [NA Hämis (7816)](https://www.nasuomi.org/kokous/na-hamis/) | Maanantai 17:30 · Turku | Empty `postinumero` | Source correction: supply the verified postcode. | Open |
| [NA-Se toimii (7571)](https://www.nasuomi.org/kokous/na-se-toimii-2/) | Maanantai 18:30 · Helsinki | Empty `postinumero` | Source correction: supply the verified postcode. | Open |
| [Perjantai yö-NA (7040)](https://www.nasuomi.org/kokous/perjantai-yo/) | Perjantai 21:30 · Helsinki | Empty `postinumero` | Source correction: supply the verified postcode. | Open |
| [NA-Керава (7034)](https://www.nasuomi.org/kokous/na-%d0%ba%d0%b5%d1%80%d0%b0%d0%b2%d0%b0/) | Keskiviikko 19:00 · Kerava | Empty `postinumero` | Source correction: supply the verified postcode. | Open |
| [NA-Pärjääkkönää (6904)](https://www.nasuomi.org/kokous/na-parjaakkonaa-3/) | Perjantai 18:00 · Oulu | Empty `postinumero` | Source correction: supply the verified postcode. | Open |
| [NA-Nuoret (6527)](https://www.nasuomi.org/kokous/na-nuoret-2/) | Keskiviikko 18:00 · Vantaa | Empty `postinumero` | Source correction: supply the verified postcode. | Open |
| [NA-Pärjääkkönää (6365)](https://www.nasuomi.org/kokous/na-parjaakkonaa/) | Sunnuntai 18:00 · Oulu | Empty `postinumero` | Source correction: supply the verified postcode. | Open |
| [Yhteinen toivo (6038)](https://www.nasuomi.org/kokous/yhteinen-toivo/) | Lauantai 16:30 · Lappeenranta | Empty `postinumero` | Source correction: supply the verified postcode. | Open |
| [NA-Pärjääkkönää (5573)](https://www.nasuomi.org/kokous/na-parjaakkonaa-2/) | Keskiviikko 18:00 · Oulu | Empty `postinumero` | Source correction: supply the verified postcode. | Open |
| [NA-Aurinko (5314)](https://www.nasuomi.org/kokous/na-aurinko/) | Maanantai 17:00 · Tampere | Empty `postinumero` | Source correction: supply the verified postcode. | Open |
| [NA-Nuoret (4396)](https://www.nasuomi.org/kokous/na-nuoret/) | Lauantai 19:00 · Vantaa | Empty `postinumero` | Source correction: supply the verified postcode. | Open |
| [Maanantai-NA (1854)](https://www.nasuomi.org/kokous/maanantai-na/) | Maanantai 20:00 · Helsinki | Empty `postinumero` | Source correction: supply the verified postcode. | Open |
| [Mitä nyt? (6467)](https://www.nasuomi.org/kokous/mita-nyt/) | Sunnuntai 17:00 · Helsinki | `postinumero=007700` | Source correction: verify the five-digit postcode; do not delete a digit by guesswork. | Open |
| [NA-Mainiemi (2867)](https://www.nasuomi.org/kokous/na-mainiemi/) | Keskiviikko 18:00 · Hämeenlinna | `postinumero=169000` | Source correction: verify the five-digit postcode; do not delete a digit by guesswork. | Open |

## Coordinates needing human review

The bounded redirect audit covered **222 physical/overseas meetings using 185 unique map URLs**. Explicit target coordinates were available for 180 meetings; **42 meetings / 37 unique URLs** remained unresolved. All 42 have map links: this is mostly a URL format/precision limitation, not missing source data or proof of a stale link. Three sampled `share.google` GET requests also ended at text Google Search results rather than target coordinates.

The table preserves all 42 affected WP identities. A human may provide verified coordinates or clarify the venue. Google map-camera `@…` and `sll=…` positions are not meeting coordinates. Existing links can also be stale, so successful extraction alone does not establish venue correctness. Internet meetings intentionally have no physical coordinates.

| Meeting | Regular schedule / source address | Evidence | Action | Status |
| --- | --- | --- | --- | --- |
| [Karjaan NA (7107)](https://www.nasuomi.org/kokous/karjaan-na/) | Maanantai 19:00 · Raasepori; Felix Fromin katu 4, 10320, Raasepori | [Original map link](https://g.co/kgs/2q8NMBH) — Google Search redirect; no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Lohja (6744)](https://www.nasuomi.org/kokous/lohjan-na/) | Perjantai 17:00 · Lohja; Kontionkatu 8, 08100, Lohja | [Original map link](https://g.co/kgs/7Dnj9hq) — Google Search redirect; no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Lohja (1013)](https://www.nasuomi.org/kokous/na-lohja-2/) | Sunnuntai 15:00 · Lohja; Kontionkatu 8, 08100, Lohja | [Original map link](https://g.co/kgs/7Dnj9hq) — Google Search redirect; no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [Perjantai yö-NA (7040)](https://www.nasuomi.org/kokous/perjantai-yo/) | Perjantai 21:30 · Helsinki; Fleminginkatu 30, Helsinki | [Original map link](https://g.co/kgs/Hu3Lhzm) — Google Search redirect; no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Керава (7034)](https://www.nasuomi.org/kokous/na-%d0%ba%d0%b5%d1%80%d0%b0%d0%b2%d0%b0/) | Keskiviikko 19:00 · Kerava; Kuparisepänkatu 3, Kerava | [Original map link](https://g.co/kgs/TyTeQCy) — Google Search redirect; no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Kellari (7492)](https://www.nasuomi.org/kokous/na-kellari/) | Lauantai 16:00 · Joensuu; Kauppakatu 35, 80100, Joensuu | [Original map link](https://g.co/kgs/cZrFbAH) — Google Search redirect; no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Saari (920)](https://www.nasuomi.org/kokous/na-akonpohja/) | Perjantai 19:00 · Parikkala; Kaivotie 1, 59510, Parikkala | [Original map link](http://goo.gl/maps/G1bYa) — Address/place identifier; no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Kuopio (6735)](https://www.nasuomi.org/kokous/na-kuopio-vain-joka-kuun-1-ja-3-lauantai/) | Lauantai 15:00 · Kuopio; Kauppakatu 46, 70110, Kuopio | [Original map link](http://goo.gl/maps/UpmWq) — Address/place identifier; no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Kuopio (3267)](https://www.nasuomi.org/kokous/na-kuopio-15/) | Sunnuntai 13:00 · Kuopio; Kauppakatu 46, 70110, Kuopio | [Original map link](http://goo.gl/maps/UpmWq) — Address/place identifier; no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Kuopio (3263)](https://www.nasuomi.org/kokous/na-kuopio-11/) | Torstai 18:00 · Kuopio; Kauppakatu 46, 70110, Kuopio | [Original map link](http://goo.gl/maps/UpmWq) — Address/place identifier; no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Jyväskylä (912)](https://www.nasuomi.org/kokous/na-jyvaskyla-3/) | Keskiviikko 18:00 · Jyväskylä; Lutakonaukio 3, 40100, Jyväskylä | [Original map link](http://goo.gl/maps/nNWD) — Address/place identifier; no explicit target point. Confirmed address mismatch: link targets Messukatu 4, source says Lutakonaukio 3. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Jyväskylä (782)](https://www.nasuomi.org/kokous/na-jyvaskyla/) | Maanantai 18:00 · Jyväskylä; Lutakonaukio 3, 40100, Jyväskylä | [Original map link](http://goo.gl/maps/nNWD) — Address/place identifier; no explicit target point. Confirmed address mismatch: link targets Messukatu 4, source says Lutakonaukio 3. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Myrri (1163)](https://www.nasuomi.org/kokous/na-myrri/) | Keskiviikko 18:00 · Vantaa; Solkikuja 8 A, 01600, Vantaa | [Original map link](https://goo.gl/maps/5ui1QiYNcB22) — Route URL has a destination token !1d/!2d, not a standard place !3d/!4d point; verify destination. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Voima (1248)](https://www.nasuomi.org/kokous/na-voima/) | Lauantai 10:00 · Helsinki; Kastelholmantie 1, 00900, Helsinki | [Original map link](https://goo.gl/maps/ynM7gPtBBRJ2) — URL contains a camera position, without an explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Hyvitys (8703)](https://www.nasuomi.org/kokous/na-hyvitys/) | Sunnuntai 15:30 · Helsinki; Myllypurontie 22, 00920, Helsinki | [Original map link](https://maps.app.goo.gl/ggZM6EagLRf3Gd3SA?g_st=am) — Address/place identifier; no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Ote (3393)](https://www.nasuomi.org/kokous/na-ote-3/) | Sunnuntai 14:30 · Mikkeli; Porrassalmenkatu 35-37, 50100, Mikkeli | [Original map link](https://maps.app.goo.gl/kCLpTK4UvBiKEueC9?g_st=ipc) — Address/place identifier; no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [Mitä Nyt? (7810)](https://www.nasuomi.org/kokous/na-mita-nyt/) | Tiistai 18:30 · Helsinki; Hietakummuntie 19B, 00770, Helsinki | [Original map link](https://share.google/6ozPK4tmmSmRdOdw7) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [Perusteksti (8962)](https://www.nasuomi.org/kokous/perusteksti/) | Maanantai 17:00 · Helsinki; Mäkelänkatu 50 B, 00510, Helsinki | [Original map link](https://share.google/BQGyGUhSZOU0mN2K2) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA Hämis (7816)](https://www.nasuomi.org/kokous/na-hamis/) | Maanantai 17:30 · Turku; Hämeenkatu 28, Turku | [Original map link](https://share.google/HObA54jpSLeU9t4hc) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Tyysteri (6104)](https://www.nasuomi.org/kokous/na-tyysteri/) | Perjantai 18:00 · Helsinki; Topparinkuja 2, 00520, Helsinki | [Original map link](https://share.google/IefSk17D5vuwhp3wZ) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA Pihlis (6197)](https://www.nasuomi.org/kokous/na-pihlis/) | Maanantai 18:00 · Helsinki; Liusketie 3 A, 00710, Helsinki | [Original map link](https://share.google/JyAciZ2GuqiCkJhRD) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [Na-Juuka (7805)](https://www.nasuomi.org/kokous/7805/) | Perjantai 18:00 · Juuka; Kokkokalliontie 3, 83900, Juuka | [Original map link](https://share.google/OPRyyhaF1fMT4C7em) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA Meri-Lappi (8886)](https://www.nasuomi.org/kokous/na-meri-lappi/) | Tiistai 18:00 · Kemi; Oklaholmankatu 22 e 1, 94700, Kemi | [Original map link](https://share.google/Qs8nJCfXOjYKjElsw) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Auringonvalo (4730)](https://www.nasuomi.org/kokous/na-auringonvalo/) | Sunnuntai 18:00 · Turku; A- kilta, Pääskyvuorenrinne 1, 20610, Turku | [Original map link](https://share.google/SR4pgpQfwXEY4FPzE) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Katko (8892)](https://www.nasuomi.org/kokous/na-katko-2/) | Tiistai 17:30 · Tampere; Vipusenkatu 6, 33530, Tampere | [Original map link](https://share.google/TMN1aZvFPPsvfcY8Q) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Meditaatio (7945)](https://www.nasuomi.org/kokous/na-meditaatio/) | Sunnuntai 15:00 · Helsinki; Haapaniemenkatu 7-9, 00530, Helsinki | [Original map link](https://share.google/UhOJI1vljj48z9tF4) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [Kouvolan-NA (1017)](https://www.nasuomi.org/kokous/kouvolan-na/) | Lauantai 18:00 · Kouvola; Savonkatu 23 ( Porukkatalo), 45100, Kouvola | [Original map link](https://share.google/aJx0CmKVuZkTTUlG7) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [Perusteksti (8964)](https://www.nasuomi.org/kokous/perusteksti-2/) | Perjantai 20:30 · Helsinki; Mäkelänkatu 50 B, 00510, Helsinki | [Original map link](https://share.google/bGbkRQpoZMjToM7oo) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Alppikylä (8738)](https://www.nasuomi.org/kokous/na-alppikyla/) | Tiistai 17:00 · Helsinki; Alppikylänkatu 22-24, 00770, Helsinki | [Original map link](https://share.google/cbPmbtMJNFcvtseXC) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [FREED🔷M (8133)](https://www.nasuomi.org/kokous/freed%f0%9f%94%b7m/) | Torstai 18:00 · Lieksa; Asema-Aukio 2, 81700, Lieksa | [Original map link](https://share.google/fBbd3tlodZ2ABlKlZ) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Kirjo (8884)](https://www.nasuomi.org/kokous/na-kirjo/) | Tiistai 18:00 · Pori; Isolinnankatu 28 (käynti Maaherrankadun puolelta), 28100, Pori | [Original map link](https://share.google/kXuHuijqkuAnLcuma) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [RYHMÄ TAI KUOLEMA (8949)](https://www.nasuomi.org/kokous/ryhma-tai-kuolema/) | Lauantai 18:00 · Helsinki; Kotkankatu 14-16, 00510, Helsinki | [Original map link](https://share.google/lnt4pnuy8gh7m3iCl) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Pitsku (8561)](https://www.nasuomi.org/kokous/na-pitsku/) | Torstai 18:30 · Helsinki; Turkismiehenkuja 4, Pitäjänmäen kirkko, 00370, Helsinki | [Original map link](https://share.google/lzXDIt4z2d6uJE0jv) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA Hämis (7820)](https://www.nasuomi.org/kokous/na-hamis-2/) | Torstai 17:30 · Turku; Hämeenkatu 28, Turku | [Original map link](https://share.google/n4L0CQwIJ1cHkjf5Z) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Nuoret (6527)](https://www.nasuomi.org/kokous/na-nuoret-2/) | Keskiviikko 18:00 · Vantaa; Orvokkitie 16, Vantaa | [Original map link](https://share.google/rKqAVbQ7IqODgQwAV) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Nuoret (4396)](https://www.nasuomi.org/kokous/na-nuoret/) | Lauantai 19:00 · Vantaa; Orvokkitie 16, Vantaa | [Original map link](https://share.google/rKqAVbQ7IqODgQwAV) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [Susirajan perjantai (963)](https://www.nasuomi.org/kokous/susirajan-perjantai/) | Perjantai 18:00 · Joensuu; Kirkkokatu 18A, 80100, Joensuu | [Original map link](https://share.google/rzLoKRTAGqwkDpW3N) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Uusialku (7941)](https://www.nasuomi.org/kokous/na-uusialku-2/) | Lauantai 19:00 · Tampere; Pitkäniemenkatu 9, 33330, Tampere | [Original map link](https://share.google/t9eTOGKkWsm2S26Z6) — Share URL; the bounded redirect audit found no explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [Puhtaat sävelet (1591)](https://www.nasuomi.org/kokous/selvat-savelet/) | Lauantai 15:00 · Pori; Veturitallinpolku 4, 28120, Pori | [Original map link](https://www.fonecta.fi/kartat/veturitallinpolku%204%20pori?lon=21.881139278411865&lat=61.51392553125196&z=15) — Fonecta lat/lon parameters may be a viewport center; verify the venue point. | Verify venue; provide a verified point or corrected link. | Open |
| [Ratkaisu (4669)](https://www.nasuomi.org/kokous/ratkaisu/) | Keskiviikko 18:00 · Helsinki; Metsolantie 14, Käpylän kirkko, 00610, Helsinki | [Original map link](https://www.google.com/maps/search/Metsolantie+14,+00610+K%C3%A4pyl%C3%A4n+kirkko,+etel%C3%A4inen+p%C3%A4%C3%A4ty/@60.6436165,24.2468535,9z/data=!3m1!4b1) — URL contains a camera position, without an explicit target point. | Verify venue; provide a verified point or corrected link. | Open |
| [NA-Sipuli (937)](https://www.nasuomi.org/kokous/na-sipuli/) | Torstai 18:00 · Kerava; Vuorelanmäki 1, 04260, Kerava | [Original map link](https://www.google.com/maps?q=Vuorelanm%C3%A4ki+1,+04260+Kerava) — Address/place identifier; no explicit target point. | Exact street/postcode/city/country also appears on resolved meeting 1009; verify before reusing its point. | Open |
| [NA-12 (3160)](https://www.nasuomi.org/kokous/na-12/) | Keskiviikko 18:00 · Tampere; Riihitie 10, 33800, Tampere | [Original map link](https://www.google.fi/maps/place/Riihitie+10,+33800+Tampere/@61.4832113,23.7987576,17z/data=!4m2!3m1!1s0x468edf4302dc2a49:0xc89e2a5432e461f6) — URL contains a camera position, without an explicit target point. | Verify venue; provide a verified point or corrected link. | Open |

Additional location concerns below include **1890 NA-Rotvalli** (the source itself says Maps is about 100 m wrong) and **6753 NaaNaNA sillaNAlla** (the actual shelter is 200 m beyond the reference building).

## Calendar, location and note interpretation

These are organizer/source questions or limits of a weekly meeting model. Preserve the notes without converting prose into automatic exception rules. The excerpt is evidence, not a proposed correction. Dates without a year must not silently be assigned 2026. Meeting start, written step work and doors opening are separate concepts.

| Meeting | Regular schedule / city | Notes / evidence | Review / action | Status |
| --- | --- | --- | --- | --- |
| [Askel Tikkari (9104)](https://www.nasuomi.org/kokous/askel-tikkari/) | Torstai 18:00 · Vantaa | “kirjoittaa … 18.35 asti … toipumiskokous klo 18.45 – 19.45”; “tarvittaessa vuosipäivinä avoin” | Policy review: the structured start describes written step work; recovery sharing starts later. Confirm what the public start should represent; Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [Porvoo NA (8985)](https://www.nasuomi.org/kokous/na-porvoo/) | Lauantai 11:00 · Porvoo | “avoin pyydettäessä ja vuosipäivillä” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [TaiteilijaNA (8931)](https://www.nasuomi.org/kokous/taiteilijana-parittomien-viikkojen-kokous/) | Torstai 18:00 · Jyväskylä | “Etäosallistumisvaihtoehto … Ryhmän oma Discord-palvelin”; “vain addikteille tai niille jotka arvelevat … huumeongelma” | Policy review: physical plus remote participation is explicit only in notes; preserve it and review hybrid classification; Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [NA-Kirjo (8884)](https://www.nasuomi.org/kokous/na-kirjo/) | Tiistai 18:00 · Pori | “parillisten viikkojen tiistaisin”; “Kokous tiistaina 15.9. … peruttu” | Policy: confirm this recurrence; a weekly row cannot enforce it; Source maintenance / clarification: past or yearless exception, not evidence of a current indefinite pause. | Open |
| [NA Kirjallisuus (8881)](https://www.nasuomi.org/kokous/kirjallisuus/) | Lauantai 16:00 · Internet | “Kirjallisuuden lukeminen alkaa klo 20:10” | Source correction / organizer clarification: structured Saturday meeting is 16:00–17:00, so this note is inconsistent. | Open |
| [NA-Hyvitys (8703)](https://www.nasuomi.org/kokous/na-hyvitys/) | Sunnuntai 15:30 · Helsinki | “Avoin 4 kertaa vuodessa … tammi- huhti- heinä- lokakuu” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [NA-Loimaa (8697)](https://www.nasuomi.org/kokous/na-loimaa/) | Sunnuntai 16:00 · Loimaa | “poikkeuksia voidaan tehdä … Ammattilaiset tai läheiset kysyttäessä” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [Puhtaana Toijalassa (8683)](https://www.nasuomi.org/kokous/8683/) | Maanantai 18:00 · Akaa | “Kokous on suljettu” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [Na Ola (7860)](https://www.nasuomi.org/kokous/na-ola/) | Maanantai 18:00 · Orimattila | “Parilliset viikot avoin … parittomat viikot suljettu” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [NA Hämis (7820)](https://www.nasuomi.org/kokous/na-hamis-2/) | Torstai 17:30 · Turku | “8.10.2026 torstaina on avoin ryhmä vuosipäivän vuoksi”; “Joka kuukauden viimeinen torstai … 17.30-18.30” | Policy review: date-specific openness is meaningful but not represented by the missing format relationship; Organizer clarification / policy review: normal duration is 90 min; a single duration cannot describe this exception. | Policy review |
| [Na-Juuka (7805)](https://www.nasuomi.org/kokous/7805/) | Perjantai 18:00 · Juuka | “kokoontuu poislukien pyhät esim. pitkäperjantai” | Policy: confirm this recurrence; a weekly row cannot enforce it. | Policy review |
| [NA Rajis (7716)](https://www.nasuomi.org/kokous/na-rajis-2/) | Tiistai 18:00 · Oulu | “Kokoukset ovat suljettuja” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [NA Vapaus (7692)](https://www.nasuomi.org/kokous/7692/) | Sunnuntai 18:00 · Pori | “kuun viimeisenä sunnuntaina teema kokous” | Policy review: the meeting subject changes on particular weeks; the weekly schedule remains valid. | Policy review |
| [Na-Laajasalon lauantai (7669)](https://www.nasuomi.org/kokous/na-laajasalon-lauantai/) | Lauantai 10:00 · Helsinki | “Kokous on avoin vuosipäivinä” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [Amour askeltyö (7615)](https://www.nasuomi.org/kokous/amour-askeltyo/) | Lauantai 18:00 · Tampere | “18.00 – 18.45 … työskentelylle … 19.00 – 20.00 NA-kokous”; “kuun ensimmäinen tiistai on avoin kokous” | Policy review: the structured start describes written step work; recovery sharing starts later. Confirm what the public start should represent; Organizer clarification: this is not a Tuesday entry. Confirm whether this is a deliberate group-wide cross-reference; do not automatically replace its weekday. | Open |
| [NAiset Vaasa (kokoontuu kuukauden viimeisenä torstaina) (7453)](https://www.nasuomi.org/kokous/naiset-vaasa/) | Torstai 18:30 · Vaasa | “kuukauden viimeisenä torstaina” | Policy: confirm this recurrence; a weekly row cannot enforce it. | Policy review |
| [NA-Majakka (7430)](https://www.nasuomi.org/kokous/na-kristiinankaupunki/) | Perjantai 18:00 · Kristiinankaupunki | “joka kuukauden toinen perjantai” | Policy: confirm this recurrence; a weekly row cannot enforce it. | Policy review |
| [NA-Valo (7335)](https://www.nasuomi.org/kokous/na-valo/) | Tiistai 18:00 · Turku | “tarvittaessa avoimia vuosipäivinä” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [NA-Kasvu (7301)](https://www.nasuomi.org/kokous/na-kasvu-3/) | Lauantai 16:00 · Jyväskylä | “17.10.2026 … kokousaika klo 12 – 13.30”; “Ensimmäisen kuukauden kokous muoto on puhuja kokous” | Organizer clarification: one-off 12:00 start instead of normal 16:00; Policy review: the meeting subject changes on particular weeks; the weekly schedule remains valid. | Open |
| [NA-Valonkantaja (6971)](https://www.nasuomi.org/kokous/na-valonkantaja-3/) | Perjantai 20:00 · Tampere | “Avoin kokous vuosipäivinä” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [NA-Pärjääkkönää (6904)](https://www.nasuomi.org/kokous/na-parjaakkonaa-3/) | Perjantai 18:00 · Oulu | “9. lokakuuta 2026 kello 18.30-20.00 … Tervakukkatie 54” | Organizer clarification: normal fields say 18:00 and another venue; preserve this one-off exception. | Open |
| [VanhempaNA (6833)](https://www.nasuomi.org/kokous/vanhempana/) | Tiistai 21:00 · Internet | “Joka kuun ensimmäinen tiistai on askel-teemainen” | Policy review: the meeting subject changes on particular weeks; the weekly schedule remains valid. | Policy review |
| [NaaNaNA sillaNAlla (6753)](https://www.nasuomi.org/kokous/naanana/) | Keskiviikko 18:00 · Kajaani | “parittomien viikkojen keskiviikkona”; “emme siis kokoonnu tuossa talossa … 200 metriä metsään” | Policy: confirm this recurrence; a weekly row cannot enforce it; Organizer clarification: Sotkamontie 13 building is a route reference, not the actual shelter venue. | Open |
| [NA-Kuopio (6735)](https://www.nasuomi.org/kokous/na-kuopio-vain-joka-kuun-1-ja-3-lauantai/) | Lauantai 15:00 · Kuopio | “Kuukauden 1. ja 3. ovat askeltyökokouksia, loput … normaaleja” | Organizer clarification / policy review: notes describe step work on the first/third Saturdays and normal meetings on the others. Confirm recurrence; the old URL slug is not current evidence. | Policy review |
| [NA-Kolo (6681)](https://www.nasuomi.org/kokous/na-kolo/) | Sunnuntai 11:45 · Espoo | “askeltyölle … 11.45-12.30 … Toipumiskokous 12.45-13.45” | Policy review: the structured start describes written step work; recovery sharing starts later. Confirm what the public start should represent. | Policy review |
| [NA-Pärjääkkönää (6365)](https://www.nasuomi.org/kokous/na-parjaakkonaa/) | Sunnuntai 18:00 · Oulu | “11. lokakuuta 2026 kello 18.00-19.30 … Latokartanontie 1”; “ensimmäinen sunnuntai … meditaatiokokous” | Organizer clarification: this Sunday uses a temporary venue; Policy review: the meeting subject changes on particular weeks; the weekly schedule remains valid. | Open |
| [Askel Tikkari (6214)](https://www.nasuomi.org/kokous/askel-tikkarina/) | Sunnuntai 16:30 · Vantaa | “16.30-17.15 … työskentelyä … 17.30-18.30 toipumiskokous”; “tarvittaessa vuosipäivinä avoin” | Policy review: the structured start describes written step work; recovery sharing starts later. Confirm what the public start should represent; Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [NA-Präntöö (HUOM! Kokoontuu jokatoinen sunnuntai, parittomilla viikoilla) (5961)](https://www.nasuomi.org/kokous/na-prantoo/) | Sunnuntai 15:00 · Vaasa | “vuonna 2026 joka toinen sunnuntai, parittomina viikkoina” | Policy: confirm this recurrence; a weekly row cannot enforce it. | Policy review |
| [Puhdas Päivä (5949)](https://www.nasuomi.org/kokous/na-puhdas-paiva-2/) | Perjantai 18:00 · Kuopio | “Kuukauden viimeinen perjantai on englanninkielinen kokous” | Organizer clarification / policy review: relationship says English and Finnish; confirm which languages apply on other Fridays. | Policy review |
| [NA-Pärjääkkönää (5573)](https://www.nasuomi.org/kokous/na-parjaakkonaa-2/) | Keskiviikko 18:00 · Oulu | “Joka kuun viimeinen keskiviikko on teemakokous” | Policy review: the meeting subject changes on particular weeks; the weekly schedule remains valid. | Policy review |
| [NA-Salo (5350)](https://www.nasuomi.org/kokous/na-kokous-salo/) | Torstai 17:00 · Salo | “Kuukauden ensimmäinen Torstain ryhmä 17:00-18:30” | Organizer clarification / policy review: normal duration is 120 min; a single duration cannot describe this exception. | Policy review |
| [NA-Aurinko (5314)](https://www.nasuomi.org/kokous/na-aurinko/) | Maanantai 17:00 · Tampere | “Avoin myös vuosipäivillä” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [Amour (5240)](https://www.nasuomi.org/kokous/na-amour-3/) | Torstai 19:00 · Tampere | “kuun ensimmäinen tiistai on avoin kokous” | Organizer clarification: this is not a Tuesday entry. Confirm whether this is a deliberate group-wide cross-reference; do not automatically replace its weekday. | Open |
| [NA-Jätkänkynttilä (5131)](https://www.nasuomi.org/kokous/na-jatkankynttila/) | Sunnuntai 17:00 · Rovaniemi | “24.10 ja 26.10 kokousmuoto on avoin” | Organizer clarification: year and relationship to this post’s regular weekday are unclear. | Open |
| [NA-Valonkantaja (4983)](https://www.nasuomi.org/kokous/na-valonkantaja-2/) | Keskiviikko 18:30 · Tampere | “Avoin kokous vuosipäivinä” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [NA- Viisari (4742)](https://www.nasuomi.org/kokous/na-viisari/) | Lauantai 17:00 · Viitasaari | “Joka kuun ensimmäinen kokous on avoin” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [Ratkaisu (4669)](https://www.nasuomi.org/kokous/ratkaisu/) | Keskiviikko 18:00 · Helsinki | “Vuosipäivinä avoin” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [NA-Laajasalo (4601)](https://www.nasuomi.org/kokous/na-laajasalo/) | Torstai 18:00 · Helsinki | “Kokouksemme on avoin vuosipäivinä” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [NA-Timma (4493)](https://www.nasuomi.org/kokous/na-timma-2/) | Tiistai 17:00 · Helsinki | “suljettuja paitsi vuosipäivinä” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [Amour (4014)](https://www.nasuomi.org/kokous/na-amour-8/) | Sunnuntai 18:00 · Tampere | “kuun ensimmäinen tiistai on avoin kokous” | Organizer clarification: this is not a Tuesday entry. Confirm whether this is a deliberate group-wide cross-reference; do not automatically replace its weekday. | Open |
| [Amour (4009)](https://www.nasuomi.org/kokous/4009/) | Torstai 13:00 · Tampere | “kuun ensimmäinen tiistai on avoin kokous” | Organizer clarification: this is not a Tuesday entry. Confirm whether this is a deliberate group-wide cross-reference; do not automatically replace its weekday. | Open |
| [NA-Etsijät (3989)](https://www.nasuomi.org/kokous/na-etsijat-3/) | Maanantai 11:00 · Turku | “Vuosipäivinä tarvittaessa avoin” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [NA-Vaasa (3913)](https://www.nasuomi.org/kokous/na-vaasa-2/) | Perjantai 19:00 · Vaasa | “Joka kuun ensimmäisenä maanantaina … kokous päättyy klo 20:00” | Organizer clarification: this post is Friday; confirm whether the Monday notice belongs here. | Open |
| [NA-Aura (3798)](https://www.nasuomi.org/kokous/na-aura-2/) | Sunnuntai 12:00 · Turku | “sunnuntaina 9.11 avoin vuosipäiväkokous”; “vuosipäivänä tarvittaessa avoimia” | Organizer clarification: year missing; 9 November 2026 is Monday. Do not apply this as a current dated event; Policy: openness/attendance is partly or only in notes; retain its meaning. | Open |
| [NA-Valonkantaja (3399)](https://www.nasuomi.org/kokous/na-valonkantaja/) | Maanantai 17:00 · Tampere | “Avoin kokous vuosipäivinä” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [Amour (3213)](https://www.nasuomi.org/kokous/na-amour-10/) | Perjantai 18:00 · Tampere | “kuun ensimmäinen tiistai on avoin kokous” | Organizer clarification: this is not a Tuesday entry. Confirm whether this is a deliberate group-wide cross-reference; do not automatically replace its weekday. | Open |
| [Amour (3206)](https://www.nasuomi.org/kokous/na-amour/) | Maanantai 19:00 · Tampere | “kuun ensimmäinen tiistai on avoin kokous” | Organizer clarification: this is not a Tuesday entry. Confirm whether this is a deliberate group-wide cross-reference; do not automatically replace its weekday. | Open |
| [NA Amor (3066)](https://www.nasuomi.org/kokous/na-fuge/) | Sunnuntai 19:00 · Fuengirola | “maa=Espanja; source supplies no timezone” | Organizer clarification: Europe/Madrid is provisional; confirm whether 19:00 is venue-local time. | Open |
| [Amour (2959)](https://www.nasuomi.org/kokous/na-amour-2/) | Lauantai 21:00 · Tampere | “kuun ensimmäinen tiistai on avoin kokous” | Organizer clarification: this is not a Tuesday entry. Confirm whether this is a deliberate group-wide cross-reference; do not automatically replace its weekday. | Open |
| [NA-Risteys (2917)](https://www.nasuomi.org/kokous/na-risteys/) | Lauantai 13:00 · Hyvinkää | “Poikkeus aikataulu 22.8.2026 … Nukarintie 32”; “Jokaisen kuun ensimmäisenä lauantaina … avoin” | Source maintenance: this temporary-venue notice is past; do not treat it as current relocation; Policy: openness/attendance is partly or only in notes; retain its meaning. | Open |
| [NA-Nurmes (2853)](https://www.nasuomi.org/kokous/na-nurmes/) | Keskiviikko 19:00 · Nurmes | “Aina tarvittaessa avoin” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [Porvoo NA (2121)](https://www.nasuomi.org/kokous/porvoo-na/) | Maanantai 18:00 · Porvoo | “avoin pyydettäessä ja vuosipäivillä” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [NA-Rotvalli (1890)](https://www.nasuomi.org/kokous/na-rotwalli/) | Sunnuntai 19:00 · Tampere | “Avoin kokous joka kuukauden 1. maanantai”; “Google Mapsia … ohjaa noin 100m väärään paikkaan” | Organizer clarification: this post is Sunday; confirm whether the text is copied or group-wide; Source correction / organizer clarification: verify the actual venue point and correct map link/override. | Open |
| [NA-Polku (1717)](https://www.nasuomi.org/kokous/na-polku/) | Torstai 18:15 · Espoo | “Vuosipäivinä saattaa myös olla avoin” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [NA-Etsijät (1414)](https://www.nasuomi.org/kokous/na-etsijat-2/) | Perjantai 11:00 · Turku | “Vuosipäivinä tarvittaessa avoin” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [NA-Voima (1248)](https://www.nasuomi.org/kokous/na-voima/) | Lauantai 10:00 · Helsinki | “Vuosipäivinä ryhmä loppuu 11.30” | Organizer clarification / policy review: normal duration is 75 min; a single duration cannot describe this exception. | Policy review |
| [NA-Myrri (1163)](https://www.nasuomi.org/kokous/na-myrri/) | Keskiviikko 18:00 · Vantaa | “Kuukauden ensimmäinen keskiviikko ja vuosipäivät avoin” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [NA-Sipuli (1009)](https://www.nasuomi.org/kokous/na-sipuli-2/) | Sunnuntai 12:00 · Kerava | “Kuukauden ensimmäinen kokous … ja vuosipäivinä myös avoin” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [NA-Jätkänkynttilä (1002)](https://www.nasuomi.org/kokous/na-jatkankynttila-2/) | Torstai 18:00 · Rovaniemi | “24.10 ja 26.10 kokousmuoto on avoin” | Organizer clarification: year and relationship to this post’s regular weekday are unclear. | Open |
| [NA-Sateenkaari (991)](https://www.nasuomi.org/kokous/na-sateenkaari/) | Lauantai 17:00 · Tampere | “Joka kuun ensimmäisenä lauantaina … kokous loppuu 18:15” | Organizer clarification / policy review: normal duration is 90 min; a single duration cannot describe this exception. | Policy review |
| [NA-Aura (954)](https://www.nasuomi.org/kokous/na-aura/) | Torstai 18:00 · Turku | “vuosipäivinä tarvittaessa avoimia” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [NA-Sipuli (937)](https://www.nasuomi.org/kokous/na-sipuli/) | Torstai 18:00 · Kerava | “Kuukauden ensimmäinen kokous … ja vuosipäivinä myös avoin” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [NA-Lohja (923)](https://www.nasuomi.org/kokous/na-lohja/) | Keskiviikko 18:00 · Lohja | “ryhmä alkaa poikkeuksellisesti 17.9 … klo 19” | Organizer clarification: year missing; 17 September 2026 is Thursday, while this post is Wednesday. | Open |
| [NA-Etsijät (915)](https://www.nasuomi.org/kokous/na-etsijat/) | Keskiviikko 18:00 · Turku | “Vuosipäivinä tarvittaessa avoin” | Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |
| [NA-Jyväskylä (912)](https://www.nasuomi.org/kokous/na-jyvaskyla-3/) | Keskiviikko 18:00 · Jyväskylä | “Kuukauden 1. kokous kestää klo 19:00” | Organizer clarification / policy review: normal duration is 90 min; a single duration cannot describe this exception. | Policy review |
| [NA-Vaasa (874)](https://www.nasuomi.org/kokous/na-vaasa/) | Maanantai 19:00 · Vaasa | “Joka kuun ensimmäisenä maanantaina … kokous päättyy klo 20:00” | Organizer clarification / policy review: normal duration is 120 min; a single duration cannot describe this exception. | Policy review |
| [NA-Jyväskylä (782)](https://www.nasuomi.org/kokous/na-jyvaskyla/) | Maanantai 18:00 · Jyväskylä | “Kuukauden 1. kokous kestää klo 19:00” | Organizer clarification / policy review: normal duration is 90 min; a single duration cannot describe this exception. | Policy review |
| [NA-Helsinki (522)](https://www.nasuomi.org/kokous/na-helsinki/) | Sunnuntai 18:00 · Helsinki | “Vuosipäivillä kokous kestää 19.45 asti”; “Vuosipäivillä kokous on avoin” | Organizer clarification / policy review: normal duration is 90 min; a single duration cannot describe this exception; Policy: openness/attendance is partly or only in notes; retain its meaning. | Policy review |

Accepted cross-reference: **6214 Askel Tikkari** announces the group’s added Thursday meeting, which exists separately as **9104**. That announcement is not a Sunday/Thursday contradiction. **1791 NA-Liekki** says its outer door stays closed; this does not mean closed attendance.

## Attendance and accessibility policy review

These notes describe real meeting conditions. A broad native format can change their meaning: “primarily women/men” is not “only women/men”, and babies under a particular age is not all children. Decide with the source maintainers whether to populate explicit source formats; retain the notes regardless. None of these observations authorizes guessed exclusions.

| Meeting | Regular schedule / city | Notes / evidence | Action | Status |
| --- | --- | --- | --- | --- |
| [NA-Valo (9095)](https://www.nasuomi.org/kokous/na-valo-2/) | Lauantai 11:30 · Turku | “Alle 1v ikäiset sylivauvat … tervetulleita” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [SavoNAiset (8980)](https://www.nasuomi.org/kokous/savonaiset/) | Keskiviikko 18:00 · Kuopio | “kaikille naiseksi identifioituville” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [TaiteilijaNA (8931)](https://www.nasuomi.org/kokous/taiteilijana-parittomien-viikkojen-kokous/) | Torstai 18:00 · Jyväskylä | “LGBTQIA+-ystävällinen” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Kirjo (8884)](https://www.nasuomi.org/kokous/na-kirjo/) | Tiistai 18:00 · Pori | “LGBTQIA+ ja nepsy-ystävällinen” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Meditaatio (7945)](https://www.nasuomi.org/kokous/na-meditaatio/) | Sunnuntai 15:00 · Helsinki | “lapsiystävällinen” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Mähän Sanoin Et Mä En Vedä Enää Kertaakaan (7934)](https://www.nasuomi.org/kokous/na-mahan-sanoin-etta-ma-en-veda-enaa-kertaakaan/) | Tiistai 17:30 · Jyväskylä | “ei saa tulla päihtyneenä, korvaushoito ok” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [Veljet (7922)](https://www.nasuomi.org/kokous/na-veljet/) | Maanantai 18:00 · Helsinki | “ensisijaisesti miehille” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [Тайна (7851)](https://www.nasuomi.org/kokous/%d1%82%d0%b0%d0%b9%d0%bd%d0%b0/) | Sunnuntai 12:15 · Helsinki | “ensisijaisesti … jotka kokevat itsensä naiseksi” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [Mitä Nyt? (7810)](https://www.nasuomi.org/kokous/na-mita-nyt/) | Tiistai 18:30 · Helsinki | “ei ole vauva tai lemmikki ystävällinen” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [Puhtaat & Rohkeat (7735)](https://www.nasuomi.org/kokous/puhtaat-rohkeat/) | Torstai 17:00 · Helsinki | “vauvaystävällisiä” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Hyvä tahto (7701)](https://www.nasuomi.org/kokous/na-hyva-tahto-2/) | Lauantai 19:15 · Tampere | “Alle 2v lapsille ok” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Säihke (7594)](https://www.nasuomi.org/kokous/na-saihke-2/) | Tiistai 17:30 · Lahti | “Ensisijaisesti naisille, mutta kaikki … ovat tervetulleita” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Sateenkaari (7514)](https://www.nasuomi.org/kokous/na-sateenkaari-2/) | Torstai 19:00 · Internet | “LGBTQIA+ Friendly” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Muutos (7069)](https://www.nasuomi.org/kokous/na-muutos/) | Keskiviikko 18:00 · Mikkeli | “0-2v vauvat ovat tervetulleita” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Valonkantaja (6971)](https://www.nasuomi.org/kokous/na-valonkantaja-3/) | Perjantai 20:00 · Tampere | “alle 2-vuotiaan lapsen kanssa” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Nuoret (6527)](https://www.nasuomi.org/kokous/na-nuoret-2/) | Keskiviikko 18:00 · Vantaa | “Vauvat taaperoikään asti … tervetulleita” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [Mitä nyt? (6467)](https://www.nasuomi.org/kokous/mita-nyt/) | Sunnuntai 17:00 · Helsinki | “ei ole vauva tai lemmikki ystävällinen” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [Na-Goton (6369)](https://www.nasuomi.org/kokous/na-goton-2/) | Lauantai 17:00 · Rauma | “Vauvat ja taaperot … tervetulleita” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [Na-Goton (6367)](https://www.nasuomi.org/kokous/na-goton/) | Tiistai 17:00 · Rauma | “Vauvat ja taaperot … tervetulleita” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA- NaiseNA Zoom (6015)](https://www.nasuomi.org/kokous/na-naisena-zoom/) | Maanantai 18:00 · Internet | “ensisijaisesti … jotka kokevat itsensä naiseksi” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Itäväylä (5955)](https://www.nasuomi.org/kokous/5955/) | Tiistai 18:00 · Helsinki | “taaperoikään asti lapset tervetulleita” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Stadin sateenkaari (5352)](https://www.nasuomi.org/kokous/na-stadin-sateenkaari/) | Keskiviikko 18:00 · Helsinki | “LGBTQIA+ friendly” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Aurinko (5314)](https://www.nasuomi.org/kokous/na-aurinko/) | Maanantai 17:00 · Tampere | “ensisijaisesti naisille … Isommat lapset … viereisessä huoneessa” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA Puhdas Voima (4985)](https://www.nasuomi.org/kokous/na-puhdas-voima/) | Torstai 18:00 · Joensuu | “Ensisijaisesti naisaddikteille suunnattu” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Valonkantaja (4983)](https://www.nasuomi.org/kokous/na-valonkantaja-2/) | Keskiviikko 18:30 · Tampere | “alle 2-vuotiaan lapsen kanssa” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA Katujätkät (4738)](https://www.nasuomi.org/kokous/na-katujatkat/) | Torstai 18:00 · Helsinki | “Taaperoikäiset ja sitä pienemmät lapset … tervetulleita” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Auringonvalo (4730)](https://www.nasuomi.org/kokous/na-auringonvalo/) | Sunnuntai 18:00 · Turku | “Sylivauvat tervetulleita” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Nuoret (4396)](https://www.nasuomi.org/kokous/na-nuoret/) | Lauantai 19:00 · Vantaa | “Vauvat taaperoikään asti … tervetulleita” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Etsijät (3989)](https://www.nasuomi.org/kokous/na-etsijat-3/) | Maanantai 11:00 · Turku | “Alle 8kk ikäiset vauvat … tervetulleita” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Valonkantaja (3399)](https://www.nasuomi.org/kokous/na-valonkantaja/) | Maanantai 17:00 · Tampere | “alle 2-vuotiaan lapsen kanssa” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Lupaus (3140)](https://www.nasuomi.org/kokous/na-lupaus-2/) | Maanantai 18:00 · Mikkeli | “Naisille suunnattu NA-kokous” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Etsijät (1414)](https://www.nasuomi.org/kokous/na-etsijat-2/) | Perjantai 11:00 · Turku | “Alle 8kk ikäiset vauvat … tervetulleita” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Voima (1248)](https://www.nasuomi.org/kokous/na-voima/) | Lauantai 10:00 · Helsinki | “toivoo, ettei ryhmään tuoda lapsia eikä eläimiä” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Sateenkaari (991)](https://www.nasuomi.org/kokous/na-sateenkaari/) | Lauantai 17:00 · Tampere | “LGBTQIA+ Friendly” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Varkaus (946)](https://www.nasuomi.org/kokous/na-kaleva/) | Torstai 18:00 · Varkaus | “alle 2v lapsen kanssa” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Etsijät (915)](https://www.nasuomi.org/kokous/na-etsijat/) | Keskiviikko 18:00 · Turku | “Alle 8kk ikäiset vauvat … tervetulleita” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Jyväskylä (912)](https://www.nasuomi.org/kokous/na-jyvaskyla-3/) | Keskiviikko 18:00 · Jyväskylä | “Sylivauvat … tervetulleita” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Hyvä tahto (905)](https://www.nasuomi.org/kokous/na-hyva-tahto/) | Tiistai 11:00 · Tampere | “Alle 2v lapsille ok” | Policy: retain this nuance; verify native format equivalence. | Policy review |
| [NA-Jyväskylä (782)](https://www.nasuomi.org/kokous/na-jyvaskyla/) | Maanantai 18:00 · Jyväskylä | “Sylivauvat … tervetulleita” | Policy: retain this nuance; verify native format equivalence. | Policy review |

| Meeting | Regular schedule / city | Notes / evidence | Action | Status |
| --- | --- | --- | --- | --- |
| [SavoNAiset (8980)](https://www.nasuomi.org/kokous/savonaiset/) | Keskiviikko 18:00 · Kuopio | “Structured label Esteellinen; shared definition describes three WCs, not accessibility” | Clarify accessibility and whether source formats should reflect this note. | Open |
| [RYHMÄ TAI KUOLEMA (8949)](https://www.nasuomi.org/kokous/ryhma-tai-kuolema/) | Lauantai 18:00 · Helsinki | “Ylin kerros, ei pääsyä pyörätuolilla” | Clarify accessibility and whether source formats should reflect this note. | Policy review |
| [NA-Kirjo (8884)](https://www.nasuomi.org/kokous/na-kirjo/) | Tiistai 18:00 · Pori | “matala kynnys, invavessaa ei ole, muutoin esteetön pääsy” | Clarify accessibility and whether source formats should reflect this note. | Policy review |
| [kotoNA (8855)](https://www.nasuomi.org/kokous/kotona-2/) | Torstai 19:00 · Kotka | “Esteetön sisäänkäynti … viereisessä rapussa” | Clarify accessibility and whether source formats should reflect this note. | Policy review |
| [NA-Pähkinä (8829)](https://www.nasuomi.org/kokous/na-pahkina/) | Sunnuntai 18:30 · Vantaa | “Esteetön pääsy, löytyy Inva WC” | Clarify accessibility and whether source formats should reflect this note. | Policy review |
| [FREED🔷M (8133)](https://www.nasuomi.org/kokous/freed%f0%9f%94%b7m/) | Torstai 18:00 · Lieksa | “esteetön pääsy. Ei inva-WC:tä” | Clarify accessibility and whether source formats should reflect this note. | Policy review |
| [KotoNA (8095)](https://www.nasuomi.org/kokous/kotona/) | Sunnuntai 18:00 · Kotka | “Esteetön sisäänkäynti … viereisessä rapussa” | Clarify accessibility and whether source formats should reflect this note. | Policy review |
| [NA-Meditaatio (7945)](https://www.nasuomi.org/kokous/na-meditaatio/) | Sunnuntai 15:00 · Helsinki | “kynnys … korkein 6,5 cm … WC … tukikaiteita” | Clarify accessibility and whether source formats should reflect this note. | Open |
| [NA-Mähän Sanoin Et Mä En Vedä Enää Kertaakaan (7934)](https://www.nasuomi.org/kokous/na-mahan-sanoin-etta-ma-en-veda-enaa-kertaakaan/) | Tiistai 17:30 · Jyväskylä | “Sisäänkäynti ei ole esteetön … toinen kerros” | Clarify accessibility and whether source formats should reflect this note. | Policy review |
| [Veljet (7922)](https://www.nasuomi.org/kokous/na-veljet/) | Maanantai 18:00 · Helsinki | “Tila on esteetön” | Clarify accessibility and whether source formats should reflect this note. | Policy review |
| [Na Ola (7860)](https://www.nasuomi.org/kokous/na-ola/) | Maanantai 18:00 · Orimattila | “Ryhmä tilaan on esteetön pääsy” | Clarify accessibility and whether source formats should reflect this note. | Policy review |
| [NAiset Vaasa (kokoontuu kuukauden viimeisenä torstaina) (7453)](https://www.nasuomi.org/kokous/naiset-vaasa/) | Torstai 18:30 · Vaasa | “Tila on esteetön” | Clarify accessibility and whether source formats should reflect this note. | Policy review |
| [NA-Hyrylä (7049)](https://www.nasuomi.org/kokous/na-hyryla/) | Maanantai 18:00 · Hyrylä | “Lähes esteetön pääsy” | Clarify accessibility and whether source formats should reflect this note. | Open |
| [Choose Life (4807)](https://www.nasuomi.org/kokous/choose-life/) | Torstai 19:00 · Maalahti | “Ei esteetöntä sisäänkäyntiä” | Clarify accessibility and whether source formats should reflect this note. | Policy review |
| [NA-Vaasa (3913)](https://www.nasuomi.org/kokous/na-vaasa-2/) | Perjantai 19:00 · Vaasa | “Tila on esteetön” | Clarify accessibility and whether source formats should reflect this note. | Policy review |
| [NA-Joensuu (2068)](https://www.nasuomi.org/kokous/na-joensuu-3/) | Sunnuntai 13:00 · Joensuu | “Structured label Esteellinen; shared definition describes three WCs, not accessibility” | Clarify accessibility and whether source formats should reflect this note. | Open |
| [NA-Vaasa (874)](https://www.nasuomi.org/kokous/na-vaasa/) | Maanantai 19:00 · Vaasa | “Tila on esteetön” | Clarify accessibility and whether source formats should reflect this note. | Policy review |

Accepted nuance: **3989 / 1414 / 915 NA-Etsijät** and **4669 Ratkaisu** have accessible-entry formats and notes saying no accessible WC. Those statements are compatible. A wheelchair-accessible entrance does not guarantee an accessible toilet. “Laitos” only establishes a treatment-facility venue; it does not establish restricted attendance.

## Accepted migration cases

These are documented compatibility choices rather than tasks to “repair” the source.

### Unspecified duration — seven meetings

| Meeting | Regular schedule / city | Source value | Handling | Status |
| --- | --- | --- | --- | --- |
| [NA-Lempäälä (9005)](https://www.nasuomi.org/kokous/na-lempaala-2/) | Sunnuntai 20:00 · Lempäälä | `kesto=0` | Store NULL; legacy API may substitute its default. | Accepted |
| [Puhtaana Toijalassa (8683)](https://www.nasuomi.org/kokous/8683/) | Maanantai 18:00 · Akaa | `kesto=0` | Store NULL; legacy API may substitute its default. | Accepted |
| [NA Uusi alku (5570)](https://www.nasuomi.org/kokous/iisalmen-na-2/) | Torstai 17:30 · Iisalmi | `kesto=0` | Store NULL; legacy API may substitute its default. | Accepted |
| [Amour (2959)](https://www.nasuomi.org/kokous/na-amour-2/) | Lauantai 21:00 · Tampere | `kesto=0` | Store NULL; legacy API may substitute its default. | Accepted |
| [NA-Liekki (1791)](https://www.nasuomi.org/kokous/na-liekki/) | Maanantai 18:00 · Tampere | `kesto=0` | Store NULL; legacy API may substitute its default. | Accepted |
| [Juuri Tänään (998)](https://www.nasuomi.org/kokous/juuri-tanaan/) | Lauantai 18:00 · Turku | `kesto=0` | Store NULL; legacy API may substitute its default. | Accepted |
| [NA-Varkaus (946)](https://www.nasuomi.org/kokous/na-kaleva/) | Torstai 18:00 · Varkaus | `kesto=0` | Store NULL; legacy API may substitute its default. | Accepted |

Only 8683 explicitly says no fixed end time. Do not infer an Open-Ended format for the other six.

### Paused meetings — seven meetings

| Meeting | Regular schedule / city | Current pause evidence | Handling / review | Status |
| --- | --- | --- | --- | --- |
| [NA-Alppikylä (8738)](https://www.nasuomi.org/kokous/na-alppikyla/) | Tiistai 17:00 · Helsinki | Resumes 01.01.2222 | Verify far-future resume/sentinel. | Open |
| [NA-Korso (7892)](https://www.nasuomi.org/kokous/na-korso/) | Tiistai 18:00 · Vantaa | Resumes 20.10.2026 | Published + Tauolla + notice; resumes on the specified date. | Accepted |
| [Mitä Nyt? (7810)](https://www.nasuomi.org/kokous/na-mita-nyt/) | Tiistai 18:30 · Helsinki | Indefinite (`Kyllä`) | Published + Tauolla + notice; indefinite flag overrides old date. | Accepted |
| [Na-Juuka (7805)](https://www.nasuomi.org/kokous/7805/) | Perjantai 18:00 · Juuka | Resumes 28.12.2028 | Verify far-future resume/sentinel. | Open |
| [NA-Koivukylä (7521)](https://www.nasuomi.org/kokous/na-koivukyla/) | Maanantai 17:30 · Vantaa | Resumes 19.10.2026 | Published + Tauolla + notice; resumes on the specified date. | Accepted |
| [NA-Katko (6609)](https://www.nasuomi.org/kokous/na-katko/) | Keskiviikko 18:00 · Vaasa | Indefinite (`Kyllä`) | Published + Tauolla + notice; indefinite pause flag. | Accepted |
| [NA-12 (3160)](https://www.nasuomi.org/kokous/na-12/) | Keskiviikko 18:00 · Tampere | Indefinite (`Kyllä`) | Published + Tauolla + notice; indefinite flag overrides old date. | Accepted |

There are 116 populated pause dates: four future, 112 past, none on the audit date. Expired dates are not active pauses and are not automatically source defects.

### Language formats — six meetings needing custom language setup

| Meeting | Regular schedule / city | Source language | Handling | Status |
| --- | --- | --- | --- | --- |
| [БлагодАрНость (8565)](https://www.nasuomi.org/kokous/%d0%b1%d0%bb%d0%b0%d0%b3%d0%be%d0%b4%d0%b0%d1%80%d0%bd%d0%be%d1%81%d1%82%d1%8c/) | Perjantai 18:00 · Helsinki | venäjä | Use the exact custom Russian language format. | Accepted |
| [Тайна (7851)](https://www.nasuomi.org/kokous/%d1%82%d0%b0%d0%b9%d0%bd%d0%b0/) | Sunnuntai 12:15 · Helsinki | venäjä | Use the exact custom Russian language format. | Accepted |
| [NA-Керава (7034)](https://www.nasuomi.org/kokous/na-%d0%ba%d0%b5%d1%80%d0%b0%d0%b2%d0%b0/) | Keskiviikko 19:00 · Kerava | venäjä | Use the exact custom Russian language format. | Accepted |
| [Choose Life (4807)](https://www.nasuomi.org/kokous/choose-life/) | Torstai 19:00 · Maalahti | ruotsi ja suomi | Use the exact custom Swedish language format alongside native Finnish. | Accepted |
| [Ge aldrig upp (4401)](https://www.nasuomi.org/kokous/ge-aldrig-upp/) | Perjantai 17:00 · Helsinki | ruotsi | Use the exact custom Swedish language format. | Accepted |
| [Bara för idag (2925)](https://www.nasuomi.org/kokous/na-bara-for-idag/) | Tiistai 19:00 · Vaasa | ruotsi | Use the exact custom Swedish language format. | Accepted |

All 240 meetings supply language information. Native Finnish/English/Persian formats are available. Conditional English on 5949 remains a separate human note-review item above.

### Source format labels — complete 116-meeting impact list

Native custom formats preserve these non-equivalent Finnish labels without changing the BMLT schema. This is an accepted implementation choice, not a claim that all 116 meetings contain errors. Conditional-open labels must not become always-open O, negative accessibility labels must not become WC, and treatment-facility labels must not become restricted attendance automatically. Source relationship IDs are unavailable; identical “Tarvittaessa avoin” labels can describe two source definitions.

Each row lists the applicable custom-label evidence. The long monthly-speaker label contains a comma as part of its name.

| Meeting | Regular schedule / city | Custom source label(s) | Status |
| --- | --- | --- | --- |
| [Askel Tikkari (9104)](https://www.nasuomi.org/kokous/askel-tikkari/) | Torstai 18:00 · Vantaa | Askeltyökokous; Ei pääsyä pyörätuolilla | Accepted |
| [Porvoo NA (8985)](https://www.nasuomi.org/kokous/na-porvoo/) | Lauantai 11:00 · Porvoo | Avoin kuun viimeinen; Avoin vuosipäivinä | Accepted |
| [SavoNAiset (8980)](https://www.nasuomi.org/kokous/savonaiset/) | Keskiviikko 18:00 · Kuopio | Avoin kuun ensimmäinen; Esteellinen | Accepted; Esteellinen meaning remains open |
| [NA-Mäntsälä mielessäin (8934)](https://www.nasuomi.org/kokous/na-mantsala-mielessain/) | Sunnuntai 18:00 · Mäntsälä | Laitos | Accepted |
| [TaiteilijaNA (8931)](https://www.nasuomi.org/kokous/taiteilijana-parittomien-viikkojen-kokous/) | Torstai 18:00 · Jyväskylä | Askeltyökokous; Tila ei ole esteetön | Accepted |
| [NA-Katko (8892)](https://www.nasuomi.org/kokous/na-katko-2/) | Tiistai 17:30 · Tampere | Laitos | Accepted |
| [NA Meri-Lappi (8886)](https://www.nasuomi.org/kokous/na-meri-lappi/) | Tiistai 18:00 · Kemi | Avoin kuun ensimmäinen | Accepted |
| [NA-Arkku (8844)](https://www.nasuomi.org/kokous/na-arkku/) | Sunnuntai 17:00 · Kankaanpää | Laitos | Accepted |
| [NA Loviisa (8786)](https://www.nasuomi.org/kokous/na-loviisa/) | Tiistai 18:00 · Loviisa | Avoin vuosipäivinä | Accepted |
| [Päivä Kerrallaan (8764)](https://www.nasuomi.org/kokous/paiva-kerrallaan/) | Sunnuntai 11:00 · Lahti | Avoin kuun ensimmäinen | Accepted |
| [NA-Loimaa (8697)](https://www.nasuomi.org/kokous/na-loimaa/) | Sunnuntai 16:00 · Loimaa | Avoin vuosipäivinä | Accepted |
| [NA Rauha (8663)](https://www.nasuomi.org/kokous/na-rauha/) | Perjantai 18:00 · Pori | Avoin vuosipäivinä | Accepted |
| [БлагодАрНость (8565)](https://www.nasuomi.org/kokous/%d0%b1%d0%bb%d0%b0%d0%b3%d0%be%d0%b4%d0%b0%d1%80%d0%bd%d0%be%d1%81%d1%82%d1%8c/) | Perjantai 18:00 · Helsinki | Avoin kuun ensimmäinen | Accepted |
| [NA-Pitsku (8561)](https://www.nasuomi.org/kokous/na-pitsku/) | Torstai 18:30 · Helsinki | Avoin kuun ensimmäinen | Accepted |
| [KotoNA (8095)](https://www.nasuomi.org/kokous/kotona/) | Sunnuntai 18:00 · Kotka | Avoin vuosipäivinä | Accepted |
| [NA-Uusialku (7941)](https://www.nasuomi.org/kokous/na-uusialku-2/) | Lauantai 19:00 · Tampere | Ei pääsyä pyörätuolilla | Accepted |
| [NA-Mähän Sanoin Et Mä En Vedä Enää Kertaakaan (7934)](https://www.nasuomi.org/kokous/na-mahan-sanoin-etta-ma-en-veda-enaa-kertaakaan/) | Tiistai 17:30 · Jyväskylä | Avoin kuun ensimmäinen; Avoin vuosipäivinä | Accepted |
| [NA Hämis (7816)](https://www.nasuomi.org/kokous/na-hamis/) | Maanantai 17:30 · Turku | Avoin kuun viimeinen | Accepted |
| [Mitä Nyt? (7810)](https://www.nasuomi.org/kokous/na-mita-nyt/) | Tiistai 18:30 · Helsinki | Ei pääsyä pyörätuolilla | Accepted |
| [NA-Hyvä tahto (7701)](https://www.nasuomi.org/kokous/na-hyva-tahto-2/) | Lauantai 19:15 · Tampere | Avoin kuun ensimmäinen | Accepted |
| [Amour askeltyö (7615)](https://www.nasuomi.org/kokous/amour-askeltyo/) | Lauantai 18:00 · Tampere | Ei pääsyä pyörätuolilla | Accepted |
| [NA-Säihke (7594)](https://www.nasuomi.org/kokous/na-saihke-2/) | Tiistai 17:30 · Lahti | Avoin kuun ensimmäinen | Accepted |
| [NA-Se toimii (7571)](https://www.nasuomi.org/kokous/na-se-toimii-2/) | Maanantai 18:30 · Helsinki | Tarvittaessa avoin | Accepted |
| [NA-Mahdollisuus (7558)](https://www.nasuomi.org/kokous/na-mahdollisuus/) | Keskiviikko 18:00 · Espoo | Ryhmässä kerran kuussa alustaja, joka jakaa kokemustaan n.15min | Accepted |
| [NA-Koivukylä (7521)](https://www.nasuomi.org/kokous/na-koivukyla/) | Maanantai 17:30 · Vantaa | Avoin kuun ensimmäinen; Avoin vuosipäivinä | Accepted |
| [NA-Majakka (7430)](https://www.nasuomi.org/kokous/na-kristiinankaupunki/) | Perjantai 18:00 · Kristiinankaupunki | Ei pääsyä pyörätuolilla | Accepted |
| [NA-Kasvu (7301)](https://www.nasuomi.org/kokous/na-kasvu-3/) | Lauantai 16:00 · Jyväskylä | Avoin kuun ensimmäinen | Accepted |
| [Karjaan NA (7107)](https://www.nasuomi.org/kokous/karjaan-na/) | Maanantai 19:00 · Raasepori | Avoin kuun ensimmäinen; Ei pääsyä pyörätuolilla | Accepted |
| [NA-Muutos (7069)](https://www.nasuomi.org/kokous/na-muutos/) | Keskiviikko 18:00 · Mikkeli | Avoin kuun viimeinen; Ei pääsyä pyörätuolilla | Accepted |
| [NA Lintta (6865)](https://www.nasuomi.org/kokous/na-lintta/) | Maanantai 18:00 · Lahti | Avoin kuun viimeinen | Accepted |
| [NA-Lohja (6744)](https://www.nasuomi.org/kokous/lohjan-na/) | Perjantai 17:00 · Lohja | Ei pääsyä pyörätuolilla | Accepted |
| [NA-Kolo (6681)](https://www.nasuomi.org/kokous/na-kolo/) | Sunnuntai 11:45 · Espoo | Askeltyökokous; Avoin kuun viimeinen | Accepted |
| [NA-Joutseno (6601)](https://www.nasuomi.org/kokous/na-joutseno/) | Maanantai 17:00 · Joutseno | Avoin kuun viimeinen | Accepted |
| [NA-Spiritti (6585)](https://www.nasuomi.org/kokous/na-spiritti/) | Maanantai 19:15 · Helsinki | Avoin vuosipäivinä; Ei pääsyä pyörätuolilla | Accepted |
| [NA-Nuoret (6527)](https://www.nasuomi.org/kokous/na-nuoret-2/) | Keskiviikko 18:00 · Vantaa | Ei pääsyä pyörätuolilla | Accepted |
| [NA-Kipinä (6521)](https://www.nasuomi.org/kokous/na-kipina/) | Lauantai 15:00 · Savonlinna | Avoin kuun ensimmäinen | Accepted |
| [Mitä nyt? (6467)](https://www.nasuomi.org/kokous/mita-nyt/) | Sunnuntai 17:00 · Helsinki | Ei pääsyä pyörätuolilla | Accepted |
| [NA-Maltsu (6414)](https://www.nasuomi.org/kokous/na-maltsu/) | Lauantai 17:00 · Helsinki | Avoin vuosipäivinä | Accepted |
| [Na-Goton (6369)](https://www.nasuomi.org/kokous/na-goton-2/) | Lauantai 17:00 · Rauma | Ei pääsyä pyörätuolilla | Accepted |
| [Na-Goton (6367)](https://www.nasuomi.org/kokous/na-goton/) | Tiistai 17:00 · Rauma | Avoin kuun ensimmäinen; Ei pääsyä pyörätuolilla | Accepted |
| [NA-pohja (6353)](https://www.nasuomi.org/kokous/na-pohja/) | Lauantai 17:00 · Seinäjoki | Avoin kuun ensimmäinen | Accepted |
| [NA-Toivo (6346)](https://www.nasuomi.org/kokous/na-toivo-4/) | Keskiviikko 18:00 · Turku | Ei pääsyä pyörätuolilla | Accepted |
| [Askel Tikkari (6214)](https://www.nasuomi.org/kokous/askel-tikkarina/) | Sunnuntai 16:30 · Vantaa | Askeltyökokous; Ei pääsyä pyörätuolilla | Accepted |
| [NA-Riksu (6151)](https://www.nasuomi.org/kokous/na-riksu/) | Torstai 19:30 · Riihimäki | Avoin kuun ensimmäinen | Accepted |
| [NA Kopukka (6137)](https://www.nasuomi.org/kokous/na-kopukka-2/) | Perjantai 15:00 · Nokia | Avoin kuun ensimmäinen | Accepted |
| [NA-Tyysteri (6104)](https://www.nasuomi.org/kokous/na-tyysteri/) | Perjantai 18:00 · Helsinki | Avoin kuun ensimmäinen | Accepted |
| [NA-Itäväylä (5955)](https://www.nasuomi.org/kokous/5955/) | Tiistai 18:00 · Helsinki | Avoin vuosipäivinä | Accepted |
| [Puhtaana Toijalassa (5755)](https://www.nasuomi.org/kokous/puhtaana-toijalassa/) | Perjantai 19:00 · Akaa | Avoin kuun ensimmäinen | Accepted |
| [NA Kopukka (5595)](https://www.nasuomi.org/kokous/na-kopukka/) | Tiistai 15:00 · Nokia | Avoin kuun ensimmäinen | Accepted |
| [NA Uusi alku (5570)](https://www.nasuomi.org/kokous/iisalmen-na-2/) | Torstai 17:30 · Iisalmi | Avoin kuun ensimmäinen; Ei pääsyä pyörätuolilla | Accepted |
| [NA-Klitsu (5366)](https://www.nasuomi.org/kokous/na-klitsu-2/) | Lauantai 12:00 · Espoo | Avoin kuun viimeinen | Accepted |
| [NA-Stadin sateenkaari (5352)](https://www.nasuomi.org/kokous/na-stadin-sateenkaari/) | Keskiviikko 18:00 · Helsinki | Avoin kuun viimeinen; Avoin vuosipäivinä | Accepted |
| [NA-Salo (5350)](https://www.nasuomi.org/kokous/na-kokous-salo/) | Torstai 17:00 · Salo | Avoin kuun ensimmäinen | Accepted |
| [NA-Aurinko (5314)](https://www.nasuomi.org/kokous/na-aurinko/) | Maanantai 17:00 · Tampere | Avoin kuun ensimmäinen | Accepted |
| [NA-Torni (5302)](https://www.nasuomi.org/kokous/na-torni/) | Keskiviikko 17:00 · Tampere | Avoin kuun ensimmäinen | Accepted |
| [Amour (5240)](https://www.nasuomi.org/kokous/na-amour-3/) | Torstai 19:00 · Tampere | Ei pääsyä pyörätuolilla | Accepted |
| [NA-Arkku (5205)](https://www.nasuomi.org/kokous/na-arkku-4/) | Torstai 18:00 · Kankaanpää | Laitos | Accepted |
| [NA-Uudelleen juurrutetut (5167)](https://www.nasuomi.org/kokous/na-uudelleen-juurrutetut-3/) | Torstai 19:00 · Mikkeli | Laitos | Accepted |
| [NA-Mäntsälä mielessäin (5141)](https://www.nasuomi.org/kokous/na-jarvenpaa/) | Tiistai 18:00 · Mäntsälä | Laitos | Accepted |
| [NA-Elämä (5134)](https://www.nasuomi.org/kokous/na-elama-2/) | Keskiviikko 18:00 · Lahti | Avoin kuun ensimmäinen | Accepted |
| [NA-Jätkänkynttilä (5131)](https://www.nasuomi.org/kokous/na-jatkankynttila/) | Sunnuntai 17:00 · Rovaniemi | Avoin kuun viimeinen | Accepted |
| [NA Katujätkät (4738)](https://www.nasuomi.org/kokous/na-katujatkat/) | Torstai 18:00 · Helsinki | Ei pääsyä pyörätuolilla | Accepted |
| [NA-Seinäjoki (4596)](https://www.nasuomi.org/kokous/na-seinajoki/) | Torstai 18:00 · Seinäjoki | Avoin kuun ensimmäinen | Accepted |
| [Yhteinen Toivo (4569)](https://www.nasuomi.org/kokous/4569/) | Tiistai 16:30 · Lappeenranta | Avoin kuun viimeinen | Accepted |
| [NA-Timma (4493)](https://www.nasuomi.org/kokous/na-timma-2/) | Tiistai 17:00 · Helsinki | Ei pääsyä pyörätuolilla | Accepted |
| [We Do Recover (4415)](https://www.nasuomi.org/kokous/we-do-recover/) | Lauantai 19:00 · Helsinki | Avoin kuun viimeinen | Accepted |
| [NA-Nuoret (4396)](https://www.nasuomi.org/kokous/na-nuoret/) | Lauantai 19:00 · Vantaa | Avoin kuun ensimmäinen; Ei pääsyä pyörätuolilla | Accepted |
| [NA-Uudelleen juurrutetut (4320)](https://www.nasuomi.org/kokous/na-uudelleen-juurrutetut-2/) | Lauantai 19:00 · Mikkeli | Laitos | Accepted |
| [NA-Uudelleen juurrutetut (4316)](https://www.nasuomi.org/kokous/na-uudelleen-juurrutetut/) | Maanantai 19:00 · Mikkeli | Laitos | Accepted |
| [NA-Jäge (4133)](https://www.nasuomi.org/kokous/na-jage/) | Perjantai 18:00 · Järvenpää | Avoin kuun ensimmäinen | Accepted |
| [Amour (4017)](https://www.nasuomi.org/kokous/na-amour-11/) | Tiistai 19:00 · Tampere | Avoin kuun ensimmäinen; Ei pääsyä pyörätuolilla | Accepted |
| [Amour (4014)](https://www.nasuomi.org/kokous/na-amour-8/) | Sunnuntai 18:00 · Tampere | Ei pääsyä pyörätuolilla | Accepted |
| [Amour (4009)](https://www.nasuomi.org/kokous/4009/) | Torstai 13:00 · Tampere | Ei pääsyä pyörätuolilla | Accepted |
| [NA-Foke (3823)](https://www.nasuomi.org/kokous/na-foke-2/) | Torstai 18:00 · Forssa | Tarvittaessa avoin | Accepted |
| [NA-Ote (3393)](https://www.nasuomi.org/kokous/na-ote-3/) | Sunnuntai 14:30 · Mikkeli | Askeltyökokous; Avoin kuun ensimmäinen | Accepted |
| [Koro-NA (3371)](https://www.nasuomi.org/kokous/koro-na-3/) | Sunnuntai 16:00 · Helsinki | Ei pääsyä pyörätuolilla | Accepted |
| [Koro-NA (3370)](https://www.nasuomi.org/kokous/koro-na-2/) | Tiistai 16:00 · Helsinki | Ei pääsyä pyörätuolilla | Accepted |
| [NA-Kuopio (3267)](https://www.nasuomi.org/kokous/na-kuopio-15/) | Sunnuntai 13:00 · Kuopio | Avoin vuosipäivinä | Accepted |
| [Amour (3213)](https://www.nasuomi.org/kokous/na-amour-10/) | Perjantai 18:00 · Tampere | Ei pääsyä pyörätuolilla | Accepted |
| [Amour (3206)](https://www.nasuomi.org/kokous/na-amour/) | Maanantai 19:00 · Tampere | Ei pääsyä pyörätuolilla | Accepted |
| [NA-12 (3160)](https://www.nasuomi.org/kokous/na-12/) | Keskiviikko 18:00 · Tampere | Ei pääsyä pyörätuolilla | Accepted |
| [NA-Rohkeus (3147)](https://www.nasuomi.org/kokous/na-rohkeus/) | Sunnuntai 17:00 · Haapajärvi | Avoin kuun viimeinen | Accepted |
| [NA-Lupaus (3140)](https://www.nasuomi.org/kokous/na-lupaus-2/) | Maanantai 18:00 · Mikkeli | Avoin kuun ensimmäinen | Accepted |
| [NA-Munkka (3132)](https://www.nasuomi.org/kokous/na-paja/) | Tiistai 18:30 · Helsinki | Avoin kuun ensimmäinen | Accepted |
| [NA-Aramesh (3062)](https://www.nasuomi.org/kokous/na-aramesh/) | Tiistai 18:30 · Helsinki | Ei pääsyä pyörätuolilla | Accepted |
| [Amour (2959)](https://www.nasuomi.org/kokous/na-amour-2/) | Lauantai 21:00 · Tampere | Ei pääsyä pyörätuolilla | Accepted |
| [NA-Yhdessä (2939)](https://www.nasuomi.org/kokous/na-yhdessa/) | Keskiviikko 18:00 · Ylivieska | Avoin kuun ensimmäinen | Accepted |
| [NA-Laakso (2923)](https://www.nasuomi.org/kokous/laakso/) | Tiistai 18:00 · Helsinki | Laitos | Accepted |
| [NA-Nurmes (2853)](https://www.nasuomi.org/kokous/na-nurmes/) | Keskiviikko 19:00 · Nurmes | Kuukauden toinen viikko avoin | Accepted |
| [Elossa (2461)](https://www.nasuomi.org/kokous/elossa/) | Torstai 17:30 · Helsinki | Avoin kuun ensimmäinen | Accepted |
| [Porvoo NA (2121)](https://www.nasuomi.org/kokous/porvoo-na/) | Maanantai 18:00 · Porvoo | Avoin kuun viimeinen; Avoin vuosipäivinä | Accepted |
| [NA-Joensuu (2068)](https://www.nasuomi.org/kokous/na-joensuu-3/) | Sunnuntai 13:00 · Joensuu | Esteellinen | Accepted; Esteellinen meaning remains open |
| [NA-Rotvalli (1890)](https://www.nasuomi.org/kokous/na-rotwalli/) | Sunnuntai 19:00 · Tampere | Avoin kuun ensimmäinen | Accepted |
| [NA-Liekki (1791)](https://www.nasuomi.org/kokous/na-liekki/) | Maanantai 18:00 · Tampere | Ei pääsyä pyörätuolilla | Accepted |
| [NA-Polku (1717)](https://www.nasuomi.org/kokous/na-polku/) | Torstai 18:15 · Espoo | Avoin kuun viimeinen; Ei pääsyä pyörätuolilla | Accepted |
| [Puhtaat sävelet (1591)](https://www.nasuomi.org/kokous/selvat-savelet/) | Lauantai 15:00 · Pori | Ei pääsyä pyörätuolilla; Laitos | Accepted |
| [NA-Myrri (1163)](https://www.nasuomi.org/kokous/na-myrri/) | Keskiviikko 18:00 · Vantaa | Avoin kuun ensimmäinen | Accepted |
| [NA-Foke (1019)](https://www.nasuomi.org/kokous/na-foke-3/) | Sunnuntai 18:00 · Forssa | Tarvittaessa avoin | Accepted |
| [NA-Lohja (1013)](https://www.nasuomi.org/kokous/na-lohja-2/) | Sunnuntai 15:00 · Lohja | Ei pääsyä pyörätuolilla | Accepted |
| [NA-Sipuli (1009)](https://www.nasuomi.org/kokous/na-sipuli-2/) | Sunnuntai 12:00 · Kerava | Avoin kuun ensimmäinen | Accepted |
| [NA-Kivi (1006)](https://www.nasuomi.org/kokous/na-kivi/) | Sunnuntai 19:00 · Helsinki | Avoin kuun ensimmäinen | Accepted |
| [NA-Jätkänkynttilä (1002)](https://www.nasuomi.org/kokous/na-jatkankynttila-2/) | Torstai 18:00 · Rovaniemi | Avoin kuun ensimmäinen | Accepted |
| [NA-Myrsky (971)](https://www.nasuomi.org/kokous/na-myrsky/) | Torstai 18:00 · Hanko | Avoin kuun ensimmäinen; Ei pääsyä pyörätuolilla | Accepted |
| [NA-Panos (956)](https://www.nasuomi.org/kokous/na-panos/) | Perjantai 18:00 · Helsinki | Avoin kuun ensimmäinen | Accepted |
| [NA-Aura (954)](https://www.nasuomi.org/kokous/na-aura/) | Torstai 18:00 · Turku | Avoin kuun ensimmäinen | Accepted |
| [NA-Varkaus (946)](https://www.nasuomi.org/kokous/na-kaleva/) | Torstai 18:00 · Varkaus | Avoin kuun viimeinen | Accepted |
| [NA-Topeliuksenkatu (940)](https://www.nasuomi.org/kokous/na-ruusulankatu-10/) | Perjantai 18:00 · Helsinki | Ei pääsyä pyörätuolilla | Accepted |
| [NA-Malmi (939)](https://www.nasuomi.org/kokous/na-malmi/) | Torstai 18:00 · Helsinki | Avoin kuun ensimmäinen; Ei pääsyä pyörätuolilla | Accepted |
| [NA-Sipuli (937)](https://www.nasuomi.org/kokous/na-sipuli/) | Torstai 18:00 · Kerava | Avoin kuun ensimmäinen; Laitos | Accepted |
| [NA-Saari (920)](https://www.nasuomi.org/kokous/na-akonpohja/) | Perjantai 19:00 · Parikkala | Avoin kuun ensimmäinen | Accepted |
| [NA-Jyväskylä (912)](https://www.nasuomi.org/kokous/na-jyvaskyla-3/) | Keskiviikko 18:00 · Jyväskylä | Avoin kuun ensimmäinen | Accepted |
| [NA-Uusi Elämä (908)](https://www.nasuomi.org/kokous/na-uusi-elama-2/) | Maanantai 18:00 · Pori | Avoin kuun ensimmäinen | Accepted |
| [NA-Hyvä tahto (905)](https://www.nasuomi.org/kokous/na-hyva-tahto/) | Tiistai 11:00 · Tampere | Avoin kuun ensimmäinen | Accepted |
| [NA-Foke (900)](https://www.nasuomi.org/kokous/na-foke/) | Tiistai 17:00 · Forssa | Avoin kuun ensimmäinen; Tarvittaessa avoin | Accepted |
| [NA-Klitsu (890)](https://www.nasuomi.org/kokous/na-klitsu/) | Tiistai 18:30 · Espoo | Avoin kuun viimeinen | Accepted |
| [NA-Helsinki (522)](https://www.nasuomi.org/kokous/na-helsinki/) | Sunnuntai 18:00 · Helsinki | Askeltyökokous | Accepted |

## Long notes and the existing admin editor

The local import preserved these complete comments in BMLT's existing long-text
storage. The normalized comments exceed the stock admin API's 512-character
write limit, including preserved links and source format/language labels. They
are valid imported records; review is needed before adopting the admin editor.

| Meeting | Regular schedule / city | Evidence | Action | Status |
| --- | --- | --- | --- | --- |
| [NA-Se toimii (7571)](https://www.nasuomi.org/kokous/na-se-toimii-2/) | Maanantai 18:30 · Helsinki | 889 characters in preserved comments | Allow longer admin writes if adopting that editor; keep relevant source text intact. | Policy review |
| [TaiteilijaNA (8931)](https://www.nasuomi.org/kokous/taiteilijana-parittomien-viikkojen-kokous/) | Torstai 18:00 · Jyväskylä | 995 characters in preserved comments | Allow longer admin writes if adopting that editor; keep relevant source text intact. | Policy review |
| [Askel Tikkari (9104)](https://www.nasuomi.org/kokous/askel-tikkari/) | Torstai 18:00 · Vantaa | 780 characters in preserved comments | Allow longer admin writes if adopting that editor; keep relevant source text intact. | Policy review |

## Resolved source history

Initially the API counted 243 posts while the public meeting page rendered 240 rows. These three published empty placeholders had no weekday, time, venue or notes. They are absent from the refreshed 240-post API and are not current migration failures.

| Historical WP ID | Meeting | Former source URL | Evidence | Status |
| --- | --- | --- | --- | --- |
| 7712 | NA Rajis | https://www.nasuomi.org/kokous/na-rajis/ | Absent from refreshed API | Resolved — 8 October 2026 |
| 7297 | NA-Kasvu | https://www.nasuomi.org/kokous/na-kasvu/ | Absent from refreshed API | Resolved — 8 October 2026 |
| 6216 | NA Steppaillen | https://www.nasuomi.org/kokous/na-steppaillen/ | Absent from refreshed API | Resolved — 8 October 2026 |

Primary sources: [published WordPress meetings](https://www.nasuomi.org/wp-json/wp/v2/kokoukset?per_page=100&page=1), [public meeting listing](https://www.nasuomi.org/kokoukset/), [source format definitions](https://www.nasuomi.org/kokousmuodot/). The API requires pagination; `per_page=1000` is rejected.
