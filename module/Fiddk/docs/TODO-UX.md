# TODO: UX-Verbesserungen (FIDDK)

Ziel: Kurzfristig wahrnehmbare Verbesserungen der Benutzerfreundlichkeit in Suche und Detailansicht erreichen, Render-Artefakte beseitigen und die Orientierung stärken – ohne größere Backend-Änderungen.

## Kurzbewertung

- Gesamteindruck: solide Struktur, klare Entitätstypen, nützliche Zusatzinfos (KVK, Datenpartner). 
- Hauptbremsen der UX: sichtbare Roh-Template-Schnipsel in Trefferlisten, redundante „Alle Treffer“-Links, nichtssagende Sortierbezeichnung („Sonstige“), Leersuche mit Millionen Treffern, Textformatierungsartefakte in Beschreibungen.

## Stärken

- Skip-Link „Weiter zum Inhalt“, Breadcrumbs, Sprachumschalter DE/EN.
- Facetten mit Zählern und „[ausschließen]“ – gute Filterbarkeit, Trunkierung vorhanden.
- Deutlich gekennzeichnete Entitätstypen (Ressource/Person/Körperschaft/Ereignis/Werk).
- Provider-/KVK-Block schafft Mehrwert in der Detailansicht.

## Problempunkte (benutzerrelevant)

- [ ] Sichtbarer Roh-Code in Trefferlisten wie: `transEsc('Call Number')?: escapeHtml($summCallNo)?` (wirkt defekt).
- [ ] Beschreibungstexte mit Backslashes/escaped Quotes (`\`, `\"`) mindern Lesbarkeit.
- [ ] Doppelte „Alle Treffer“-Schaltflächen (Seitenleiste und Pagination) – redundant.
- [ ] Sortieroption „Sonstige“ ist unklar; Nutzer erwarten sprechende Labels (z. B. Datum/Titel).
- [ ] Leersuche (lookfor leer) führt zu ~2,2 Mio. Treffern – wenig hilfreich und teuer.
- [ ] Lange Listen in „Ähnliche Einträge“ ohne „mehr anzeigen“ – Scrolllast, v. a. mobil.
- [ ] Teilweise generische alt-Texte wie `alt="Icon"` statt leer (dekorativ) oder semantisch (z. B. „Kalender“).

## Quick Wins (geringer Aufwand, hohe Wirkung)

- [ ] Render-Artefakte in Trefferliste entfernen:
  - Ursache liegt in Treffer-Teiltemplates (RecordDriver/…/result-list*.phtml). Prüfen, ob unkommentierte Debug-Zeilen oder fehlende Echo/Übersetzungsaufrufe vorhanden sind.
- [ ] Redundanz „Alle Treffer“ reduzieren:
  - Einen der Buttons entfernen (Empfehlung: in Pagination entfernen und nur in der Sidebar anbieten).
- [ ] Beschreibungstexte säubern:
  - Beim Rendern Slashes entfernen (z. B. `stripcslashes`) und dann escapen; horizontale Trenner optisch korrekt darstellen (nicht im Fließtext).
- [ ] Sortierlabels überarbeiten:
  - „Sonstige“ durch konkrete Optionen ersetzen oder ausblenden, wenn die Sortierung nicht sinnvoll unterstützt wird.
- [ ] Alt-Texte korrigieren:
  - Dekorative Icons: `alt=""` (oder role="presentation"); inhaltliche Icons sinnvoll benennen (z. B. „Kalender“ bei Datumsangaben).
- [ ] Leersuche im Blender abfangen:
  - Bei leerem „lookfor“ statt Volltrefferliste einen Hinweis anzeigen (z. B. „Bitte geben Sie einen Suchbegriff ein“) bzw. Start-Empfehlungen.

## Mittelfristige Maßnahmen

- [ ] Facetten-Links glätten (Blender-Backend-Werte mit technischen Platzhaltern in Ziel-URLs nicht in sichtbare Texte laufen lassen).
- [ ] „Ähnliche Einträge“ anfalten:
  - Initial 5–10 anzeigen, dann „Mehr anzeigen“/Collapsible.
- [ ] Internationalisierung vereinheitlichen:
  - Label- und Datums-Konsistenz, insbesondere für Sorten und Schaltflächen.

## Konkrete Datei-Hinweise (bereits vorhanden)

- themes/fiddk/templates/search/results.phtml
  - [ ] Leersuche: bei Backend „Blender“ und leerem Suchbegriff Hinweise statt Volltrefferliste.
  - [ ] Seitenleisten-Link „Alle Treffer“ beibehalten, Doppelung in Pagination eliminieren.
- themes/fiddk/templates/search/pagination.phtml
  - [ ] Spezialblock „Alle Treffer“ (Blender + >400) entfernen, um Redundanz zu vermeiden.
- themes/fiddk/templates/RecordDriver/SolrEdm/data-collapsible.phtml
  - [ ] Vor dem Escapen `stripcslashes()` anwenden; Alt-Label-Sprachauswahl ist bereits robust (Präfix „de“).
- themes/fiddk/templates/RecordDriver/DefaultRecord/data-related-events.phtml
  - [ ] Alt-Text der Kalender-Icons semantisch setzen (leer oder „Kalender“).

## Benötigte weitere Dateien für zielgenaue Fixes (bitte hinzufügen)

- [ ] themes/fiddk/templates/search/list.phtml (Listenansicht)
- [ ] themes/fiddk/templates/search/list-grid.phtml (Gridansicht, falls genutzt)
- [ ] themes/fiddk/templates/RecordDriver/DefaultRecord/result-list.phtml (oder entsprechendes result-Partial)
- [ ] optional: themes/fiddk/templates/search/controls/sort.phtml (Sortierlabels)

Diese Partials sind die wahrscheinlichsten Quellen für die sichtbaren Roh-Code-Stellen („Call Number“).

## Messbare Ziele

- [ ] 0 Vorkommen sichtbarer Template-Schnipsel in Trefferlisten.
- [ ] Reduzierter Bounce bei Leersuche (Hinweis statt Volltrefferliste).
- [ ] Weniger Scrolling in Detailansicht (anfaltbare „Ähnliche Einträge“).
- [ ] Klare Sortierlabels – reduzierte Fehlbedienungen in Usability-Tests.

## Offene Fragen

- [ ] Sollen Leersuchen grundsätzlich verhindert oder durch „Entdecken“-Module ersetzt werden?
- [ ] Gewünschte Standard-Sortierung pro Entitätstyp (Datum, Titel, Relevanz)?
- [ ] Maximale Anzahl initial angezeigter „Ähnliche Einträge“?

(Stand: aktueller Review der gelieferten Seiten und Template-Inhalte)
