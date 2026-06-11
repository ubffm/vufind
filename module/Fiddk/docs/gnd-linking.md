# Technische Zusammenfassung: GND-basiertes Linking in VuFind ohne Überschreiben der Partnerdaten

## Ausgangslage

Im Portal existiert bereits eine Sonderlogik für IDs mit dem Präfix `gnd_`.

Aktuelles Verhalten:

```text
Record-ID beginnt mit gnd_
→ lobid wird geladen
→ lobid/GND-Daten werden als primäre Anzeige verwendet
→ ursprüngliche Partnerdaten werden nicht mehr angezeigt
```

Deshalb dürfen lokale Partner-Agenten **nicht** einfach auf `gnd_...` als primäre ID umgestellt werden.

Beispiel:

```text
Nicht tun:
rwm_10005 → gnd_118522213
```

Denn das würde den Partner-Agenten faktisch in einen lobid/GND-Record verwandeln.

Stattdessen sollen lokale Partnerdaten erhalten bleiben und die GND nur als zusätzliche Linking-ID verwendet werden.

---

## Zielmodell

Es werden zwei Identitätsebenen unterschieden:

| Ebene             | Zweck                                     | Beispiel        |
| ----------------- | ----------------------------------------- | --------------- |
| lokale Partner-ID | stabile Identität im Datenpartner-Kontext | `rwm_10005`     |
| GND-Linking-ID    | partnerübergreifende Verknüpfung          | `gnd_118522213` |

Ein Agent bleibt lokal eindeutig:

```text
https://performing-arts.eu/discovery/agent/rwm_10005
```

Wenn eine GND vorhanden ist, wird sie zusätzlich aus `owl:sameAs` extrahiert:

```turtle
<https://performing-arts.eu/discovery/agent/rwm_10005>
    owl:sameAs <http://d-nb.info/gnd/118522213> .
```

Daraus wird:

```text
gnd_118522213
```

Diese GND-Linking-ID wird in den **Titeldokumenten** indexiert, nicht nur im Agent-Datensatz.

---

## Warum die GND in den Titeldaten stehen muss

VuFind/Solr findet Titel über den bibliografischen Index. Wenn die GND nur im Agent-Datensatz steht, müsste die Anwendung zur Laufzeit erst auflösen:

```text
Titel → lokaler Agent → Agent-Datensatz → GND → weitere Titel
```

Das ist unnötig kompliziert.

Stattdessen soll jedes Titeldokument direkt enthalten:

```json
{
  "author_id": [
    "https://performing-arts.eu/discovery/agent/rwm_10005"
  ],
  "author_gnd_id": [
    "gnd_118522213"
  ]
}
```

Dann kann VuFind direkt suchen:

```text
author_gnd_id:"gnd_118522213"
```

und bekommt alle Titel aller Datenpartner, die auf dieselbe GND verweisen.

---

## Aktueller Zustand im Titeldokument

Beispiel aus dem aktuellen Index:

```json
{
  "id": "rwm_153286",
  "record_format": "edm",
  "author": [
    "Leopold Demuth",
    "None Verwaltung der Bühnenfestspiele Bayreuth"
  ],
  "author_id": [
    "https://performing-arts.eu/discovery/agent/rwm_10005",
    "https://performing-arts.eu/discovery/agent/rwm_18552"
  ],
  "author_role": [
    "contributor",
    "contributor"
  ]
}
```

Die Felder `author`, `author_id` und `author_role` sind positionsbasiert zu lesen:

```text
author[0]      = Leopold Demuth
author_id[0]   = https://performing-arts.eu/discovery/agent/rwm_10005
author_role[0] = contributor

author[1]      = None Verwaltung der Bühnenfestspiele Bayreuth
author_id[1]   = https://performing-arts.eu/discovery/agent/rwm_18552
author_role[1] = contributor
```

Für die Anzeige und Linkerzeugung sollte diese positionsbasierte Zuordnung erhalten bleiben.

---

## Schema-Erweiterung

Im aktuellen Solr-Schema existiert bereits:

```xml
<field name="author_id" type="string" indexed="true" stored="true" multiValued="true"/>
```

Dieses Feld ist technisch geeignet für exakte ID-Suchen. Es sollte aber nicht mit lokaler ID und GND-Linking-ID vermischt werden.

Empfohlene Ergänzung direkt nach `author_id`:

```xml
<field name="author_gnd_id" type="string" indexed="true" stored="true" multiValued="true"/>
<field name="author_gnd_id_display" type="string" indexed="false" stored="true" multiValued="true"/>
```

Kontext im Schema:

```xml
<field name="publisher_id" type="string" indexed="true" stored="true" multiValued="true"/>
<field name="author_id" type="string" indexed="true" stored="true" multiValued="true"/>
<field name="author_gnd_id" type="string" indexed="true" stored="true" multiValued="true"/>
<field name="author_gnd_id_display" type="string" indexed="false" stored="true" multiValued="true"/>
<field name="topic_id" type="string" indexed="true" stored="true" multiValued="true"/>
```

### Feldbedeutung

| Feld                    | Zweck                                     | indexed | stored | multiValued |
| ----------------------- | ----------------------------------------- | ------: | -----: | ----------: |
| `author_id`             | lokale Agent-URI                          |      ja |     ja |          ja |
| `author_gnd_id`         | GND-Linking für Suche/Filter              |      ja |     ja |          ja |
| `author_gnd_id_display` | positionsgleiche GND für Template/Anzeige |    nein |     ja |          ja |

---

## Warum zwei GND-Felder?

`author_gnd_id` enthält nur tatsächlich vorhandene GNDs:

```json
"author_gnd_id": [
  "gnd_118522213"
]
```

Dieses Feld dient der Suche:

```text
author_gnd_id:"gnd_118522213"
```

`author_gnd_id_display` bleibt positionsgleich zu `author`, `author_id` und `author_role`:

```json
"author_gnd_id_display": [
  "gnd_118522213",
  ""
]
```

Damit kann das Template beim Rendern der zweiten Person erkennen: Diese Person hat keine GND, also Fallback auf lokale ID.

---

## Zielzustand im Titeldokument

Beispiel, wenn `rwm_10005` eine GND hat und `rwm_18552` keine:

```json
{
  "id": "rwm_153286",
  "record_format": "edm",
  "author": [
    "Leopold Demuth",
    "Verwaltung der Bühnenfestspiele Bayreuth"
  ],
  "author_id": [
    "https://performing-arts.eu/discovery/agent/rwm_10005",
    "https://performing-arts.eu/discovery/agent/rwm_18552"
  ],
  "author_role": [
    "contributor",
    "contributor"
  ],
  "author_gnd_id": [
    "gnd_118522213"
  ],
  "author_gnd_id_display": [
    "gnd_118522213",
    ""
  ]
}
```

Wenn beide Agenten eine GND haben:

```json
{
  "author_gnd_id": [
    "gnd_118522213",
    "gnd_123456789"
  ],
  "author_gnd_id_display": [
    "gnd_118522213",
    "gnd_123456789"
  ]
}
```

Wenn kein Agent eine GND hat:

```json
{
  "author_gnd_id": [],
  "author_gnd_id_display": [
    "",
    ""
  ]
}
```

---

## Indexing-Logik

Beim Aufbau des Titeldokuments muss aus den Agentendaten eine Lookup-Tabelle erzeugt werden:

```python
agent_to_gnd = {
    "https://performing-arts.eu/discovery/agent/rwm_10005": "gnd_118522213",
    "https://performing-arts.eu/discovery/agent/rwm_18552": None,
}
```

Dann werden die Titeldaten angereichert:

```python
authors: list[str] = []
author_ids: list[str] = []
author_roles: list[str] = []
author_gnd_ids: list[str] = []
author_gnd_ids_display: list[str] = []

for contributor in contributors:
    author_uri = contributor.uri
    gnd_id = agent_to_gnd.get(author_uri)

    authors.append(contributor.label)
    author_ids.append(author_uri)
    author_roles.append(contributor.role)

    if gnd_id:
        author_gnd_ids.append(gnd_id)
        author_gnd_ids_display.append(gnd_id)
    else:
        author_gnd_ids_display.append("")
```

Wichtig:

```text
author_gnd_id
→ nur echte GND-Werte

author_gnd_id_display
→ positionsgleich zu author/author_id/author_role
```

---

## Extraktion der GND aus `owl:sameAs`

Aus einer URI wie:

```text
http://d-nb.info/gnd/118522213
```

oder:

```text
https://d-nb.info/gnd/118522213
```

wird:

```text
gnd_118522213
```

Beispielcode:

```python
from __future__ import annotations

import re

GND_URI_RE = re.compile(r"https?://d-nb\.info/gnd/([^/#?]+)")


def extract_gnd_id(uri: str) -> str | None:
    match = GND_URI_RE.match(uri)
    if match is None:
        return None

    return match.group(1)


def build_gnd_linking_id(uri: str) -> str | None:
    gnd_id = extract_gnd_id(uri)
    if gnd_id is None:
        return None

    return f"gnd_{gnd_id}"
```

---

## VuFind-Linklogik

Beim Rendern von Personenlinks wird bevorzugt über die GND verlinkt.

Logik:

```text
Wenn author_gnd_id_display[i] vorhanden:
    Link auf author_gnd_id:"gnd_..."
sonst:
    Link auf author_id:"lokale Agent-URI"
```

Beispiel:

```text
Leopold Demuth
→ /Search/Results?filter[]=author_gnd_id:"gnd_118522213"

Verwaltung der Bühnenfestspiele Bayreuth
→ /Search/Results?filter[]=author_id:"https://performing-arts.eu/discovery/agent/rwm_18552"
```

PHP-Skizze:

```php
$authors = $this->driver->tryMethod('getAuthors');
$authorIds = $this->driver->tryMethod('getAuthorIds');
$authorRoles = $this->driver->tryMethod('getAuthorRoles');
$authorGndIds = $this->driver->tryMethod('getAuthorGndIdsDisplay');

foreach ($authors as $i => $author) {
    $localId = $authorIds[$i] ?? null;
    $gndId = $authorGndIds[$i] ?? null;

    if (!empty($gndId)) {
        $field = 'author_gnd_id';
        $value = $gndId;
    } else {
        $field = 'author_id';
        $value = $localId;
    }

    // Link auf Search/Results mit filter[]=$field:"$value"
}
```

---

## Absicherungspunkte (verbindlich)

Zur robusten Umsetzung werden folgende Punkte verbindlich festgelegt:

1. **Konsistente Normalisierung der GND-URI**
   - `http://d-nb.info/gnd/...` und `https://d-nb.info/gnd/...` werden gleich behandelt.
   - Optionaler Trailing-Slash, Query-Parameter und Fragment werden bei der Extraktion ignoriert.
   - Ergebnis wird einheitlich als `gnd_<id>` gespeichert.

2. **Mehrfaches `owl:sameAs` pro Agent**
   - Falls mehrere `owl:sameAs`-Werte vorhanden sind, werden nur gültige GND-URIs berücksichtigt.
   - Wenn genau eine gültige GND gefunden wird, wird diese übernommen.
   - Wenn mehrere unterschiedliche GND-IDs gefunden werden, wird deterministisch entschieden (z. B. erste nach stabiler Sortierung) und ein Warn-Log geschrieben.
   - Ziel: reproduzierbares Verhalten bei fehlerhaften/mehrdeutigen Daten.

3. **Leere Werte in `author_gnd_id_display`**
   - Für „keine GND vorhanden“ wird verbindlich der leere String `""` verwendet (nicht `null`).
   - Dadurch bleibt die Positionsgleichheit zu `author`, `author_id`, `author_role` stabil.

4. **Reindex-Strategie**
   - Nach Einführung der Felder ist ein Reindex der betroffenen Titeldokumente verpflichtend.
   - Empfohlen: Full-Reindex des bibliografischen Kerns, wenn die Datenherkunft oder Mapping-Logik geändert wurde.
   - Delta-Reindex nur dann, wenn sichergestellt ist, dass alle betroffenen Records neu geschrieben werden.

---

## Abfragebeispiele

### Partnerübergreifende Suche über GND

```text
author_gnd_id:"gnd_118522213"
```

Findet Titel von beliebigen Datenpartnern, sofern sie dieselbe GND im Feld `author_gnd_id` haben.

### Lokale Suche ohne GND

```text
author_id:"https://performing-arts.eu/discovery/agent/rwm_18552"
```

Findet Titel, die auf diesen konkreten lokalen Partner-Agenten verweisen.

---

## Bestehende `gnd_...`-Sonderlogik bleibt erhalten

Die bisherige Logik sollte weiter nur für echte primäre GND-Records gelten:

```text
record.id startsWith "gnd_"
→ lobid als Primärquelle laden
```

Für Partner-Records mit GND gilt dagegen:

```text
record.id = rwm_10005
author_gnd_id = gnd_118522213
→ Partnerdaten anzeigen
→ GND nur für Linking und optionale Anreicherung nutzen
```

Dadurch entstehen drei saubere Fälle:

| Fall                   | Primäre ID      | Anzeige      | lobid    |
| ---------------------- | --------------- | ------------ | -------- |
| echter Normdatenrecord | `gnd_118522213` | lobid/GND    | primär   |
| Partner-Agent mit GND  | `rwm_10005`     | Partnerdaten | optional |
| Partner-Agent ohne GND | `rwm_18552`     | Partnerdaten | nein     |

---

## Korrekturhinweis: `None Verwaltung...`

Im aktuellen Beispiel steht:

```json
"None Verwaltung der Bühnenfestspiele Bayreuth"
```

Das sieht nach einem Mapping-Bug aus. Vermutlich wird ein optionaler Namensbestandteil ungeprüft konkateniert.

Statt:

```python
label = f"{prefix} {name}"
```

sollte defensiv gebaut werden:

```python
parts = [prefix, name]
label = " ".join(part for part in parts if part)
```

Ziel:

```json
"Verwaltung der Bühnenfestspiele Bayreuth"
```

---

## Umsetzungsschritte

1. **Solr-Schema erweitern**

   Ergänzen:

   ```xml
   <field name="author_gnd_id" type="string" indexed="true" stored="true" multiValued="true"/>
   <field name="author_gnd_id_display" type="string" indexed="false" stored="true" multiValued="true"/>
   ```

2. **Agent-Indexing erweitern**

   Aus `owl:sameAs` GND-URIs extrahieren:

   ```text
   http://d-nb.info/gnd/118522213
   → gnd_118522213
   ```

3. **Titel-Indexing erweitern**

   Für jeden `dc:contributor`:

   ```text
   lokale Agent-URI → Lookup in Agent-GND-Mapping → author_gnd_id
   ```

4. **Titeldokumente neu indexieren**

   Die GND-Linking-ID muss im bibliografischen Solr-Core stehen.

5. **RecordDriver erweitern**

   Methoden ergänzen, z. B.:

   ```php
   getAuthorGndIds()
   getAuthorGndIdsDisplay()
   ```

6. **Templates anpassen**

   Personenlinks bevorzugt über `author_gnd_id`, Fallback über `author_id`.

7. **Bestehende `gnd_...`-Logik unverändert lassen**

   Aber nur für primäre Record-IDs verwenden, nicht für Partner-Agenten.

8. **Testfälle prüfen**

   * Agent mit GND
   * Agent ohne GND
   * zwei Partner mit derselben GND
   * gleichnamige Personen mit unterschiedlicher GND
   * mehrere Autoren pro Titel
   * Autor mit fehlender GND zwischen zwei Autoren mit GND
   * Agent mit mehreren `owl:sameAs`, davon mehrere/keine gültige GND
   * Mischfälle `http`/`https`/Trailing-Slash in GND-URIs
   * Konsistente Behandlung leerer Display-Werte (`""` statt `null`)

---

## Testfall

### Titel Partner A

```json
{
  "id": "rwm_153286",
  "author": ["Leopold Demuth"],
  "author_id": ["https://performing-arts.eu/discovery/agent/rwm_10005"],
  "author_gnd_id": ["gnd_118522213"],
  "author_gnd_id_display": ["gnd_118522213"]
}
```

### Titel Partner B

```json
{
  "id": "foo_98765",
  "author": ["Leopold Demuth"],
  "author_id": ["https://performing-arts.eu/discovery/agent/foo_777"],
  "author_gnd_id": ["gnd_118522213"],
  "author_gnd_id_display": ["gnd_118522213"]
}
```

### Erwartung

Diese Suche:

```text
author_gnd_id:"gnd_118522213"
```

liefert beide Titel:

```text
rwm_153286
foo_98765
```

Die Anzeige der einzelnen Partnerdatensätze bleibt unverändert partnerbasiert.

---

## Kurzfassung

Die Umsetzung besteht aus einer klaren Trennung:

```text
author_id
→ lokale Partner-Agent-ID

author_gnd_id
→ partnerübergreifende GND-Linking-ID

author_gnd_id_display
→ positionsgleiche GND für Template-Anzeige
```

Die primäre Record-ID bleibt lokal:

```text
rwm_10005
```

Die GND wird nur zusätzlich indexiert:

```text
gnd_118522213
```

VuFind verlinkt Personen dann bevorzugt über:

```text
author_gnd_id:"gnd_118522213"
```

und fällt bei fehlender GND zurück auf:

```text
author_id:"https://performing-arts.eu/discovery/agent/rwm_10005"
```

Damit bleiben Partnerdaten sichtbar, während die partnerübergreifende Verknüpfung über GND funktioniert.
