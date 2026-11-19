# VuFind Theme Development Tools

Werkzeuge zur Analyse und zum visuellen Vergleich der VuFind-Themes bei der Migration von **VuFind 10.2 (Bootstrap 3 / LESS)** auf **VuFind 11.1 (Bootstrap 5 / SCSS)**.

Ziel ist es, CSS- und Layout-Abweichungen zwischen beiden Versionen systematisch zu erkennen und die Ergebnisse für Coding-Agenten wie aider oder Claude Code bereitzustellen.

## 1. Verzeichnisstruktur

```text
local/tools/
├── README.md
├── test-browser.mjs
├── compare-css.mjs
└── css-report/
    ├── old/
    │   ├── screenshot.png
    │   ├── styles.json
    │   └── page.html
    ├── new/
    │   ├── screenshot.png
    │   ├── styles.json
    │   └── page.html
    ├── diff.json
    └── diff.md
```

Die Dateien unter `css-report/` werden automatisch generiert.

## 2. Voraussetzungen

- Node.js
- npm
- Playwright
- Lokal installierter Chromium- oder Chrome-Browser
- Zwei erreichbare VuFind-Instanzen

Standardkonfiguration:

| Version | URL | Framework |
|---|---|---|
| VuFind 10.2 | http://localhost:8001 | Bootstrap 3 / LESS |
| VuFind 11.1 | http://localhost:8000 | Bootstrap 5 / SCSS |

### Playwright installieren

Im VuFind-Projektverzeichnis:

```bash
npm install --save-dev playwright
```

Das Skript verwendet einen lokal installierten Chromium-/Chrome-Browser. Ein zusätzlicher Browserdownload über `npx playwright install` ist nicht erforderlich.

Browserpfad prüfen:

```bash
command -v chromium
command -v chromium-browser
command -v google-chrome
```

## 3. Browser-Test

Mit `test-browser.mjs` lässt sich prüfen, ob Playwright auf die lokale VuFind-Installation zugreifen kann.

```bash
node local/tools/test-browser.mjs
```

Bei erfolgreichem Aufruf wird der Seitentitel ausgegeben:

```text
Seitentitel: Suchergebnisse :: FID Darstellende Kunst
```

Zusätzlich erstellt das Testskript einen Screenshot unter `/tmp/vufind-test.png`.

**Hinweis:** Das Testskript verwendet einen fest konfigurierten Chromium-Pfad, der gegebenenfalls angepasst werden muss.

## 4. CSS-Vergleich

Das Skript `compare-css.mjs` vergleicht die gerenderte Darstellung beider VuFind-Versionen.

Es erfasst:

- Screenshots der vollständigen Seiten
- HTML-Dokumente
- DOM-Elemente und CSS-Klassen
- Geometrische Eigenschaften (Position, Breite, Höhe)
- Berechnete CSS-Eigenschaften (`getComputedStyle()`)
- Unterschiede zwischen beiden Versionen
- Nicht zugeordnete Elemente

### Standardaufruf

Alle Befehle werden aus dem VuFind-Projektverzeichnis ausgeführt.

```bash
node local/tools/compare-css.mjs
```

Standardmäßig wird folgende Seite verglichen:

```text
/Search/Results?lookfor=&type=AllFields&limit=20
```

Viewport: 1588 × 960 Pixel.

### Eine bestimmte Suchanfrage vergleichen

```bash
node local/tools/compare-css.mjs \
  --page "/Search/Results?lookfor=theater&type=AllFields"
```

### Mobile Darstellung vergleichen

```bash
node local/tools/compare-css.mjs \
  --width 390 \
  --height 844 \
  --output local/tools/css-report-mobile
```

### Browserpfad explizit angeben

```bash
node local/tools/compare-css.mjs \
  --browser /usr/bin/chromium
```

### Alle Optionen

| Option | Standardwert | Beschreibung |
|---|---|---|
| `--old` | `http://localhost:8001` | URL der alten Version |
| `--new` | `http://localhost:8000` | URL der neuen Version |
| `--page` | `/Search/Results?...` | Zu vergleichender Seitenpfad |
| `--output` | `local/tools/css-report` | Ausgabeverzeichnis |
| `--width` | `1588` | Viewport-Breite |
| `--height` | `960` | Viewport-Höhe |
| `--limit` | `12` | Maximale Anzahl an Elementen pro Selektor |
| `--browser` | automatische Erkennung | Pfad zu Chromium/Chrome |
| `--help` | – | Hilfe anzeigen |

## 5. Generierte Reports

### `old/screenshot.png` und `new/screenshot.png`

Vollständige Screenshots der beiden Versionen bei identischer Viewport-Größe.

Sie dienen der visuellen Kontrolle und als Referenz bei der Korrektur des neuen Themes.

### `old/styles.json` und `new/styles.json`

Enthalten die erfassten DOM-Elemente, ihre Geometrie und die berechneten CSS-Eigenschaften.

Beispiel:

```json
{
  "rect": {
    "x": 120,
    "y": 80,
    "width": 1200,
    "height": 60
  },
  "styles": {
    "display": "flex",
    "font-size": "16px",
    "padding-top": "10px"
  }
}
```

### `old/page.html` und `new/page.html`

HTML-Snapshots der gerenderten Seiten.

Sie ermöglichen die Untersuchung von strukturellen Änderungen zwischen Bootstrap 3 und Bootstrap 5.

### `diff.json`

Maschinenlesbarer Vergleich der erfassten Elemente.

Enthält:

- Unterschiede bei CSS-Eigenschaften
- Positions- und Größenabweichungen
- Zuordnungen zwischen alten und neuen DOM-Elementen
- Nicht zugeordnete Elemente
- Bewertung der geometrischen Abweichungen

### `diff.md`

Markdown-Report für Entwickler und Coding-Agenten.

Die Abweichungen werden nach Komponenten gegliedert, beispielsweise `header`, `container`, `search`, `sidebar` und `results`.

Jede Änderung wird nach einem einfachen Schema klassifiziert:

| Kategorie | Geometrische Abweichung |
|---|---|
| CRITICAL | mindestens 50 px |
| HIGH | mindestens 15 px |
| MEDIUM | mindestens 5 px |
| LOW | CSS-Unterschied ohne größere Geometrieabweichung |

Diese Kategorien sind technische Heuristiken und keine Bewertung der tatsächlichen visuellen Relevanz.

## 6. Untersuchte Komponenten

Die zu untersuchenden DOM-Elemente sind in der Konstante `COMPONENTS` innerhalb von `compare-css.mjs` definiert.

Standardmäßig werden unter anderem untersucht:

- Body
- Header und Navigation
- Logo
- Container
- Suchformular und Suchbuttons
- Seitentitel
- Breadcrumbs
- Hauptinhalt
- Sidebar und Facetten
- Suchergebnisse
- Buttons
- Feedback-Elemente

Die CSS-Selektoren können und sollten bei Bedarf an das eigene Theme `fiddk` angepasst werden.

Beispiel:

```javascript
const COMPONENTS = {
  header: [
    "#header",
    ".navbar",
    ".my-custom-header",
  ],
  results: [
    ".result",
    ".result-body",
  ],
};
```

**Wichtig:** Die Zuordnung von Elementen zwischen beiden Versionen erfolgt heuristisch anhand von IDs, Attributen, Klassen, Textinhalten und gegebenenfalls der Reihenfolge.

Gerade bei unterschiedlichen Bootstrap-Versionen kann eine Zuordnung fehlerhaft sein. Elemente mit geringer Zuordnungssicherheit sollten manuell geprüft werden.

## 7. Verwendung mit Coding-Agenten

Die Reports können verwendet werden, um Abweichungen im Theme `fiddk` gezielt zu korrigieren.

### aider

```bash
aider \
  --read local/tools/css-report/diff.md \
  --read local/tools/css-report/diff.json
```

Die betroffenen SCSS- oder Template-Dateien können anschließend zum Bearbeiten hinzugefügt werden.

### Beispiel-Prompt

> Analysiere die CSS-Unterschiede zwischen VuFind 10.2 und VuFind 11.1 anhand des Reports `local/tools/css-report/diff.md`.
>
> Untersuche zunächst Containerbreiten, Grid und Header.
>
> Ermittle die Ursachen in den SCSS-Dateien und Templates des Themes `fiddk`.
>
> Korrigiere die Abweichungen möglichst ohne `!important` und ohne Änderungen an VuFind-Core-Dateien.
>
> Berücksichtige, dass das alte Theme Bootstrap 3 und das neue Bootstrap 5 verwendet.
>
> Führe nach den Korrekturen den Vergleich erneut aus.

### Empfohlener Arbeitsablauf

1. Beide VuFind-Versionen starten.
2. CSS-Vergleich ausführen.
3. `diff.md` und Screenshots analysieren.
4. Die größten Layoutabweichungen priorisieren.
5. SCSS-Dateien oder Templates korrigieren.
6. VuFind-CSS neu kompilieren.
7. Vergleich erneut ausführen.
8. Verbleibende Unterschiede prüfen.

Es empfiehlt sich, pro Iteration nur einen Bereich wie Header, Grid, Facetten oder Ergebnislisten zu bearbeiten.

## 8. Grenzen des Vergleichs

### Computed Styles statt SCSS-Quellen

Das Skript liest die endgültigen CSS-Werte aus dem Browser.

Es ermittelt nicht automatisch, welche SCSS-Regel oder Quelldatei für einen Wert verantwortlich ist.

Hierfür kann das Werkzeug später um das Chrome DevTools Protocol (CDP) erweitert werden.

### Unterschiedliche DOM-Strukturen

Bootstrap 3 und Bootstrap 5 können unterschiedliche HTML-Strukturen verwenden. Eine exakte Elementzuordnung ist daher nicht in allen Fällen möglich.

### Dynamische Inhalte

Abweichende Trefferzahlen, wechselnde Inhalte, verzögert geladene Elemente oder unterschiedliche Webfonts können die Vergleichsergebnisse beeinflussen.

Für möglichst reproduzierbare Vergleiche sollten identische Daten und Viewport-Einstellungen verwendet werden.

### Visuelle Gleichheit

Gleiche Computed Styles bedeuten nicht zwangsläufig eine pixelgenau gleiche Darstellung. Umgekehrt können unterschiedliche CSS-Werte visuell gleichwertige Ergebnisse erzeugen.

Der Report ist daher ein Diagnosewerkzeug und kein vollständiger automatischer visueller Regressionstest.

## 9. Versionsverwaltung

Die generierten Reports sollten normalerweise nicht eingecheckt werden, da sie umfangreich sein können und lokale URLs sowie HTML-Inhalte enthalten.

Empfohlene Einträge für `.gitignore`:

```gitignore
/local/tools/css-report/
/local/tools/css-report-*/
```

Die Werkzeuge selbst (`*.mjs` und `README.md`) können versioniert werden.

## 10. Mögliche Erweiterungen

- Export der angewendeten CSS-Regeln über CDP
- Zuordnung von CSS-Regeln zu Stylesheets
- Unterstützung für CSS-Source-Maps zur Rückverfolgung nach SCSS
- Automatischer visueller Screenshot-Diff
- Vergleich mehrerer VuFind-Seiten in einem Testlauf
- Responsive Tests für verschiedene Viewports
- Kompakter Report mit priorisierten Layoutabweichungen
- Automatische Regressionstests mit Playwright

---

**Projekt:** VuFind Theme `fiddk`  
**Migration:** VuFind 10.2 → VuFind 11.1  
**Frontend:** Bootstrap 3 / LESS → Bootstrap 5 / SCSS  
**Testwerkzeug:** Playwright / Node.js