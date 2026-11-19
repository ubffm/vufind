
# LinkChecker meldet HTTP 500 bei VuFind-Suchformular

## Problem

Beim Prüfen der VuFind-Startseite mit LinkChecker 10.6.0 wird folgender Fehler gemeldet:

```text
URL:      /Combined/SearchBox
Ergebnis: 500 Internal Server Error
```

Das Suchformular auf der Startseite funktioniert im Browser jedoch problemlos.

## Ursache

Das Suchformular verwendet die Route `/Combined/SearchBox` als Formular-Action:

```html
<form method="get" action="/Combined/SearchBox">
```

Beim regulären Absenden übermittelt der Browser zusätzliche GET-Parameter, insbesondere `type`:

```text
/Combined/SearchBox?lookfor=theater&type=VuFind%3ASolr%7CAllFields
```

LinkChecker prüft dagegen die Formular-Action ohne Parameter:

```text
/Combined/SearchBox
```

Im `CombinedController.php` (Zeile 273) erwartet VuFind einen entsprechend formatierten `type`-Parameter:

```php
[$type, $target] = explode(
    ':',
    $this->params()->fromQuery('type'),
    2
);
```

Fehlt dieser Parameter, liefert `explode()` keinen zweiten Array-Eintrag. Dadurch entsteht die PHP-Warnung `Undefined array key 1`, die als Exception behandelt wird und zum HTTP-500-Fehler führt.

## Bewertung

- **LinkChecker:** Prüft die Formular-Action ohne die erforderlichen Parameter. Dadurch entsteht ein Fehlalarm bezüglich der Funktionsfähigkeit des Suchformulars.
- **VuFind:** Das Suchformular funktioniert regulär. Allerdings behandelt der Controller fehlende oder ungültige Parameter nicht robust und antwortet mit HTTP 500 statt beispielsweise HTTP 400.
- **Auswirkung:** Keine erkennbare Beeinträchtigung der regulären Suchfunktion.

Es handelt sich somit primär um eine Einschränkung der Linkprüfung und sekundär um eine kleinere Schwachstelle in der Eingabevalidierung von VuFind.

## Lösung

Die Formular-Action kann bei der Linkprüfung ausgeschlossen werden:

```bash
linkchecker -r 1 \
    --ignore-url '/Combined/SearchBox' \
    http://localhost:8000/
```

Eine Änderung am VuFind-Suchformular ist nicht erforderlich. Optional könnte der `CombinedController` um eine Validierung des `type`-Parameters erweitert werden, um ungültige Aufrufe mit HTTP 400 statt HTTP 500 zu beantworten.