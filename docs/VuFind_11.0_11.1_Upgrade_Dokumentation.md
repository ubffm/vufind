# VuFind 11.0 und 11.1 – Upgrade-Dokumentation

> **Zweck:** Technische Zusammenfassung der Änderungen und erforderlichen Prüfungen für ein Upgrade auf VuFind 11.0 bzw. 11.1. Grundlage sind die beiden Release-Mitteilungen und die zugehörigen ausführlichen Video-Transkripte. Die Dokumentation unterscheidet zwischen zwingenden Migrationsschritten, von der lokalen Installation abhängigen Anpassungen und optionalen Neuerungen.
>
> **Stand der ausgewerteten Releases:** VuFind 11.0 vom 24. November 2025; VuFind 11.1 vom 20. Juli 2026. **Keine Prüfung einer konkreten Installation** und keine Aussage über nachträglich veröffentlichte Korrekturen.

## 1. Management Summary

**VuFind 11.0 ist ein umfangreiches Major-Release mit tiefgreifenden technischen Änderungen.** Insbesondere wurden die Datenbankzugriffe von `laminas-db` auf Doctrine umgestellt, weitere Laminas-Komponenten ersetzt, das Solr-Schema modernisiert und die Bootstrap-3-Unterstützung entfernt. **VuFind 11.1** ergänzt Funktionen und Konfigurationsmöglichkeiten, führt aber ebenfalls Änderungen ein, die beim Upgrade zu berücksichtigen sind – vor allem eine erneute Solr-Neuindexierung sowie ein neues Cookie-Consent-System.

Die wichtigste Konsequenz für die Planung:

1. **Upgrade-Pfad:** Vor VuFind 11.0 muss die Installation auf VuFind 10 aktualisiert werden. Für lokale Anpassungen sind die in VuFind 10 bzw. 10.2 bereitgestellten Migrationsmöglichkeiten besonders wichtig.
2. **Daten und Suche:** Datenbankmigrationen durchführen, Solr-Schema und Konfigurationen aktualisieren und die geforderte **vollständige Neuindexierung** einplanen. Auch VuFind 11.1 verlangt eine Neuindexierung.
3. **Lokaler Code:** Eigenentwicklungen auf alte Laminas-Schnittstellen, geänderte Methodensignaturen, Composer-Autoloading und die Bootstrap-5-/AssetManager-Migration prüfen.
4. **Funktionstests:** Suche, Benutzerkonto, Authentifizierung, ILS-Funktionen, lokale Themes, Cookie-Consent und Monitoring gezielt testen.
5. **VuFind 12 vorbereiten:** Mehrere in 11.x noch unterstützte Alt-Schnittstellen sind bereits als veraltet gekennzeichnet.

### 1.1 Priorisierung

| Priorität | Thema | Version | Wann relevant? | Erforderliche Aktion |
|---|---|---|---|---|
| **P0** | Upgrade über VuFind 10 | 11.0 | Immer bei älteren Ausgangsversionen | Zuerst VuFind 10 erreichen und Migrationen dort vorbereiten |
| **P0** | PHP-/Datenbankanforderungen | 11.0 | Immer | PHP mindestens 8.2; MySQL 5 nicht mehr unterstützt; MariaDB korrekt konfigurieren |
| **P0** | Datenbankmigration | 11.0 | Bei Datenbanknutzung | Neue Doctrine-basierte Migrationen ausführen und überprüfen |
| **P0** | Solr-Schema und Neuindexierung | 11.0 | Bei Solr-Nutzung | Schema 1.7 berücksichtigen und vollständigen Reindex durchführen |
| **P0** | Solr-Neuindexierung | 11.1 | Bei Solr-Nutzung | Änderungen an Formaterkennung und Solr-Konfiguration übernehmen und neu indexieren |
| **P1** | Doctrine-Migration eigener DB-Zugriffe | 11.0 | Bei lokalem PHP-Code mit `laminas-db` | Datenbankzugriffscode migrieren |
| **P1** | Bootstrap 5 / AssetManager | 11.0 | Bei lokalen Themes oder Templates | Bootstrap-3-Reste und alte Asset-Helper entfernen |
| **P1** | Veraltete Passwortverschlüsselung | 11.0 | Bei gespeicherten verschlüsselten Passwörtern | `legacyPbkdf2` prüfen und Hash-/Verschlüsselungsdaten mit vorgesehenem CLI-Werkzeug aktualisieren |
| **P1** | Cookie Consent | 11.1 | Insbesondere bei lokal angepasster Consent-Oberfläche | Darstellung, Consent-Erkennung und lokale Anpassungen testen |
| **P1** | Auth-, ILS- und Service-Signaturen | 11.0/11.1 | Bei eigenen Klassen/Treibern | Abgeleitete Methoden und Konstruktoren mit Core vergleichen |
| **P2** | Optionale Features / Konfiguration | 11.0/11.1 | Je nach Anwendungsfall | Einsatz gesondert entscheiden |
| **P2** | Deprecations für VuFind 12 | 11.0/11.1 | Für Zukunftssicherheit | Technische Schulden frühzeitig abbauen |

> **Hinweis zur Planung:** Beide Transkripte fordern für die jeweilige Version eine Solr-Neuindexierung. Für einen zusammenhängenden Upgrade-Pfad bis 11.1 sollte geprüft werden, wann die endgültige Indexierung sinnvoll durchgeführt werden kann. Die Quellen garantieren nicht, dass bei jeder konkreten Migrationsstrategie eine einzige Indexierung ausreicht.

## 2. Voraussetzungen und Upgrade-Ablauf

### 2.1 Reihenfolge

- Ausgangsversion, lokale Module, Themes, Solr-Konfiguration, ILS-Anbindung und Authentifizierungsmethoden erfassen.
- Bei einer Ausgangsversion vor 10 zunächst auf **VuFind 10** aktualisieren. Dort die für Version 11 notwendigen Anpassungen durchführen. Die Texte nennen insbesondere die Bootstrap-3-zu-5-Migration und den in VuFind 10.2 eingeführten `AssetManager`.
- Vor dem Upgrade Datenbank, Suchindex, lokale Konfigurationen, Themes und Eigenentwicklungen sichern und einen Wiederherstellungsweg verifizieren.
- Änderungen an PHP-/Datenbankumgebung und lokalem Code umsetzen.
- Upgrade- und Datenbankmigrationsschritte durchführen. VuFind 11 bietet dafür auch Kommandozeilenwerkzeuge; die vormals webbasierten Werkzeuge bleiben verfügbar.
- Solr-Schema/-Konfiguration aktualisieren und Neuindexierung durchführen.
- Das im 11.0-Transkript ausdrücklich empfohlene Kommando `util/update_resource_metadata` ausführen, um Metadaten von in der Datenbank gespeicherten Ressourcen (z. B. für Favoritenlisten) mit Such-Backends zu synchronisieren.
- Integrations-, Sicherheits-, Oberflächen- und Regressionstests durchführen.

**Nicht aus den Quellen ableitbar:** Die exakten Aufrufparameter der Upgrade- und Migrationswerkzeuge, Wartungsfenster, Laufzeiten und ein installationsspezifischer Rollback-Ablauf. Diese müssen anhand der tatsächlich eingesetzten Version und Betriebsumgebung festgelegt werden.

### 2.2 Laufzeit und Datenbank

| Komponente | Änderung in 11.0 | Konsequenz |
|---|---|---|
| PHP | Unterstützung für PHP 8.1 entfällt; Mindestanforderung 8.2, verbesserte Unterstützung für 8.4 | Version und PHP-Erweiterungen prüfen |
| MySQL | MySQL 5 wird nicht mehr unterstützt; MySQL 8 genannt | Vorher Datenbankversion prüfen |
| MariaDB | In der Datenbankkonfiguration explizit `MariaDB` statt `MySQL` verwenden | Konfiguration überprüfen, besonders bei SolrMarc |
| PostgreSQL / MySQL | Vereinheitlichte datenbankübergreifende Migrationen | Migrationsstatus kontrollieren |
| PHP 8.5 | VuFind 11.1 verbessert Kompatibilität | Dies ist eine Kompatibilitätsverbesserung, **keine** im Text genannte neue Mindestversion |

### 2.3 Datenbankmigration und Schema

- **`laminas-db` wird durch Doctrine ersetzt.** Database-Service-Interna wurden neu implementiert. Die alten Namespaces `VuFind\Db\Row` und `VuFind\Db\Table` sind entfernt; entsprechende Entity- und Service-Konzepte übernehmen die Aufgaben.
- Das Upgradeverfahren nutzt **Migrationsskripte** sowohl für MySQL als auch für PostgreSQL; angewendete Migrationen werden nachverfolgt.
- Neue Tabellen u. a. für Audit-Events und Online-Zahlungen; außerdem Aufnahme der Tabelle `log` in das Standardschema.
- Änderungen an `resource` (u. a. Aktualisierungszeit und Anzeigetitel), OAI-Resumption-Token-Daten und `user_list`; zusätzliche Beziehung zwischen `search` und `user`; vereinheitlichte PostgreSQL-Definitionen und Indexanpassungen.
- Sonderfall: Wer während der Entwicklung von Version 11 aus dem Entwicklungszweig eine Datenbankmigration ausgeführt hat, muss ggf. manuelle Anpassungen vornehmen. Das Transkript beschreibt dies ausdrücklich als Sonderfall, nicht als regulären Release-Upgrade-Schritt.

**Prüfung bei Eigenentwicklungen:** Direkte SQL-Zugriffe, eigene Row-/Table-Klassen und Erweiterungen von Datenbankservices auf Doctrine-/Entity-/Service-APIs umstellen. Eigene Schemaerweiterungen nach der Migration validieren.

### 2.4 Solr und Indexierung

**VuFind 11.0**

- Solr-Schema auf Version **1.7** modernisiert; vollständige Neuindexierung notwendig.
- `docValues` sind bei vielen Feldern standardmäßig aktiviert. Das kann den Index auf dem Datenträger vergrößern und den Heap-Verbrauch verringern; nach der Migration Speicherverteilung und Performance überwachen.
- Formatbestimmung für elektronische Zeitschriften/Serien verbessert; Ähnlichkeitssuche berücksichtigt Signaturen besser, einschließlich Dewey und Library of Congress.
- Unterstützung für Datumsbereichsfelder im Solr-Schema ergänzt.

**VuFind 11.1**

- Erneute Neuindexierung laut Transkript erforderlich: Verbesserte Formatfacetten-Erkennung (u. a. Datensätze und Videospiele) sowie modernisierte Solr-Standardkonfiguration zur Vermeidung veralteter Einstellungen und Warnungen.

**Testfälle:** Anzahl und Abrufbarkeit indexierter Datensätze, Suche, Facetten, Formate, Sortierungen, ähnliche Titel, Zeitfilter sowie relevante lokale Indexierungsregeln.

## 3. Inkompatible Änderungen an lokalem PHP-Code

### 3.1 Laminas, Konfiguration und Dependency Injection

| Bisher | Neu / Änderung | Migrationsbedarf |
|---|---|---|
| `laminas-db` | Doctrine | Eigene Low-Level-Datenbankzugriffe ersetzen |
| `Laminas\Config\Config` | `VuFind\Config\Config` oder Konfigurationsarrays | Signaturen, Konstruktoren und Zugriffe anpassen |
| Controller-Helper `getConfig()` | Bevorzugt `getConfigArray()` | Bei eigenen Controllern Umstieg empfehlen; alter Helper zunächst nicht vollständig entfernt |
| `VuFind\Config\PluginManager` | `VuFind\Config\ConfigManager` / `ConfigManagerInterface` | Abhängigkeiten und Typdeklarationen prüfen; alter PluginManager zunächst kompatibel, aber zur Ablösung vorgesehen |
| `laminas-crypt` | Native PHP-Alternativen und lokale Ersatzklassen | Eigene Kryptographie-/Auth-Integrationen prüfen |
| `laminas-loader` | Composer-Autoloading | `getAutoloaderConfig` in eigenen Modulen durch Composer-Konfiguration ersetzen |
| `laminas-log` | Monolog / PSR-3 | Logger, Log-Level, Custom Handler; `error()` statt `err()` |
| Laminas HTTP (teilweise) | Schrittweise Guzzle-Migration | `CachingDownloader`-Callbacks und HTTP-Ausnahmebehandlung prüfen |
| `Laminas\I18n\Translator\TranslatorInterface` | `Laminas\Translator\TranslatorInterface` | Referenzen in lokalen Klassen anpassen |
| `VuFind\Config\Locator` | `VuFind\Config\PathResolver` | Ersetzten statischen Zugriff prüfen |

Weitere Änderungen:

- `VuFind\Config\Feature\IniReaderTrait` entfernt; für direkte INI-Verarbeitung verweist das Transkript auf PHPs `parse_ini_file`.
- `VuFind\Config\Reader\CacheDecorator` entfernt.
- `VuFind\Cache\Manager` besitzt geänderte Konstruktor-/Factory-Signaturen.
- Konfigurations-Caching ist optional neu verfügbar.
- `ConfigUpgrade` wurde vereinfacht; eigene Unterklassen müssen mögliche Signaturänderungen berücksichtigen.
- Die Traits `BulkActionControllerTrait` und `BulkActionTrait` erwarten im entsprechenden Kontext `$this->configManager` statt `$this->configLoader`.
- `VuFind\Http\CachingDownloader` nutzt Guzzle; `VuFind\Exception\HttpDownloadException` verwendet für Header ein Array statt `Laminas\Http\Headers`.
- In VuFind 11.1 kommt eine `AutowiringFactory` hinzu. Sie wird zunächst begrenzt eingesetzt; sie ist vor allem als künftige Vereinfachung eigener Factories interessant.

### 3.2 Typisierung, Methoden und Signaturen

Mit VuFind 11.0 wurden viele zuvor implizit nullable PHP-Parameter explizit nullable gemacht, unter anderem wegen PHP-8.4-Deprecation-Warnungen. **Bei abgeleiteten Klassen muss die Signaturkompatibilität überprüft werden.** Das Transkript nennt beispielsweise:

- `SearchServiceInterface::getSearches()` (nullable Session-ID)
- Entity-Interfaces in `VuFind\Db` (statische Rückgabetypen)
- `VuFind\Export` (Methodentypen, Konstruktor, `getBulkUrl`)
- Solr-Backend-Methode `terms`
- `VuFind\RecordTab\TabManager`
- `VuFind\Record\ResourcePopulator`
- `VuFind\OAuth2\Entity\ScopeEntity`
- `RecordDataFormatter\SpecBuilder`
- View-Helper `Related` und `linkify`
- `VuFindTheme\Initializer` (u. a. `getThemeOptions`, `sendThemeOptionsToView`)
- `getHiddenSortOptions` (geändertes Rückgabeformat)
- OAI-Resumption-Service `createAndPersistToken` (Zeitzonenbehandlung)

Für VuFind 11.1 werden zusätzlich eine vereinheitlichte Signatur von `getDefaultRequestGroup` in ILS-Treibern und ein geänderter Konstruktor von `HoldingsILS` genannt.

### 3.3 Logging und Sicherheit

- In 11.0 Monolog und PSR-3 übernehmen; eigene Log-Handler müssen ggf. komplett umgestellt werden.
- 11.1 unterstützt granularer alle PSR-3-Log-Level, erhält aber die historischen kombinierten Log-Level aus Kompatibilitätsgründen.
- Optionales Audit-Event-Logging kann u. a. Login- und Zahlungsvorgänge nachvollziehbar machen.
- API-Key-Unterstützung für öffentliche APIs ist optional.
- Verbesserter Rate Limiter mit möglicher Cloudflare-Turnstile-Integration.
- Passwort-Recovery gibt standardmäßig keine Auskunft darüber, ob ein Konto existiert (`recover_be_honest` ändert dieses Verhalten). Standardgültigkeit von Recovery-Links in 11.0 auf **eine Stunde** statt zwei Wochen reduziert (`recover_hash_lifetime`).
- `legacyPbkdf2` nach dem Upgrade prüfen: Das 11.0-Transkript empfiehlt, die Altkompatibilität zu deaktivieren und die vorhandenen verschlüsselten Passwortdaten mit dem entsprechenden CLI-Werkzeug neu zu erzeugen. Der Wegfall der Altunterstützung wird für das nachfolgende Major-Release angekündigt.
- `SystemStatus`-AJAX-Handler in 11.1 prüft `access.SystemStatus` in `permissions.ini`; Monitoring-Zugriff gezielt erlauben bzw. sonst einschränken.

## 4. Themes, Templates und Frontend

### 4.1 Migration auf Bootstrap 5

VuFind 11.0 entfernt die Bootstrap-3-basierten Themes `bootstrap3`, `bootprint3` und `sandal` und die zuvor in VuFind 10 vorhandene Bootstrap-3-Kompatibilitätsschicht des Bootstrap-5-Themes.

**Aufgaben bei lokalen Themes:**

1. Prüfen, von welchem Basistheme das lokale Theme erbt.
2. Bootstrap-3-spezifisches Markup, Klassen und JavaScript migrieren.
3. Assets über den seit VuFind 10.2 vorhandenen **`AssetManager`** einbinden, nicht mehr über `inlineScript`, `headLink`, `headScript` oder `footScript`.
4. Eigene Template-Overrides mit VuFind 11.x vergleichen und Such-, Detail-, Benutzerkonto- und Formularansichten testen.

### 4.2 Weitere UI-Änderungen

**VuFind 11.0:**

- Neugestaltung der **Channels**-Darstellung ohne Slick-Karussell; statt `channel_size` werden `items_per_row`, `rows_per_page` und `max_batch_size` konfiguriert. `SimilarItemsCarousel` wurde durch einen Channels-Record-Tab ersetzt.
- Neue Breadcrumb-View-Helper; die bisherige Vorgehensweise bleibt zunächst möglich.
- Modernisierte Datums-Slider und `PubDateVisAjax`-Darstellung.
- Template `Helpers/star-rating.phtml` in den Komponentenbereich `_ui/components` verschoben.
- Item-Statusanzeige und Konto-Alerts stärker serverseitig gerendert; lokale JS-/Template-Anpassungen prüfen.
- `AvailabilityStatus::availabilityAsString()` liefert nun `available`/`unavailable` statt `true`/`false` als Strings.
- Überarbeitete Paginierung; CSS-Klasse `.checkboxFilter` zu `.checkbox-filters` geändert.
- `extendedHtmlAttributeEscaping` ist konfigurierbar und standardmäßig deaktiviert. Bei lokalen Templates auf korrekt quotierte HTML-Attribute achten.
- `AccountMenu.yaml` unterstützt eine neue Abschnittsstruktur; vorhandene Einstellungen sollten weiter funktionieren, sollten aber bei Anpassungen überprüft werden.

**VuFind 11.1:**

- **Cookie Consent komplett neu implementiert.** Vorherige Consent-Cookies bleiben lesbar; Darstellung und lokale Customizations können Anpassungen erfordern.
- **FontAwesome 7:** Eigene Icons und CSS auf Funktion und Erscheinungsbild testen.
- Navigations-Helper wurden in die allgemeineren **Section-Helper** integriert; lokale Plugins prüfen.
- Header und Footer optional über `HeaderBar.yaml` und `FooterMenu.yaml` konfigurierbar.
- Entfernen von **Grunt** aus dem Build-Prozess; eigene Build-Abläufe auf `npm run` bzw. npm-Skripte umstellen.

## 5. Authentifizierung und Bibliothekssysteme

### 5.1 Authentifizierung

**11.0:**

- Änderungen an `AuthenticationManager` sowie an Auth-Interfaces und Handler-Signaturen, etwa `getPasswordPolicy`, `supportsPasswordRecovery` und `validatePasswordAgainstPolicy`.
- Handler-Methoden `resetState` und `logout` wurden in `clearLoginState` und `getLogoutRedirectUrl` umbenannt; neuer Check `hasSessionInitiator` und geändertes Verhalten von `getSessionInitiator`.
- Passwort-Recovery-E-Mail-Template vom allgemeinen `email`-Verzeichnis nach `Auth/Database` verlegt.
- Passwortreset über ILS-Treiber ist möglich, sofern der Treiber dies unterstützt.

**11.1:**

- E-Mail-Authentifizierung/-Validierung verwendet **Einmalpasswörter (OTP)** statt Magic Links, da Link-Previews von Mailclients die Links ungültig machen konnten.
- Damit verbundener Magic-Link-Code in Auth-Helpern bzw. Authenticatoren entfernt.
- Für die Authentifizierungsart `PasswordAccess` können Zugangsdaten mit `access_user_hashed` als Hash konfiguriert werden.

### 5.2 ILS-Treiber und Integrationen

- **11.0:** Zeitgesteuerte Sperrung einzelner ILS-Funktionen (treiberabhängig), Integration von EDS-Such-Backends mit ILS-Funktionen, überarbeitete gemeinsame ILS-Rückgabestrukturen (`createPatronArray`, `createProfileArray`), Änderungen an `ILSAuthenticator` sowie an FOLIO-Methoden und Bestands-/Statusabfrage.
- **11.1:** FOLIO-Treiber für die im Transkript genannte **Sunflower**-Version weiterentwickelt, inklusive Lieferoptionen, Sortierung von Ausleihen und optionalem Webhook bei Bestellungen; Koha-Treiber mit verbesserter Steuerung von Übersetzungen.
- **11.1:** Neuer EBSCO-LinkIQ-Linkresolver; verbesserter EDS-Cover-Loader mit Fallback-Möglichkeiten; differenziertere Filterung von BrowZine-/LibKey-Links.

**Testen, sofern genutzt:** Benutzerdaten, Ausleihen, Vormerkungen, Gebühren, Passwort-Reset, Bestand/Verfügbarkeit, FOLIO-/Koha-Sonderfunktionen, Linkresolver und EDS.

## 6. Neue und optionale Funktionen

Diese Änderungen sind interessant, **aber für ein reines technisches Upgrade nicht automatisch zu konfigurieren**.

### 6.1 Neu in VuFind 11.0

| Funktion | Beschreibung |
|---|---|
| Online-Gebührenzahlung | Pluginbasiert; PayTrail, Stripe und Demo-Zahlungsdienst genannt |
| ProQuest Federated Search Gateway | Neues Suchbackend für ProQuest-Datenbanken |
| Benutzerverwaltete Inhalte | Nutzer können Kommentare, Ratings und Tags prüfen bzw. entfernen |
| Audit Events | Optionales datenbankbasiertes Ereignisprotokoll |
| Public API Keys | Optionaler Zugriffsschutz für öffentliche APIs |
| Konfigurations-Caching | Optionales Caching beim Laden der Konfiguration |
| Record Data Formatter | Konfiguration auch nach Record-Driver differenzierbar |
| Identifier Linker | Ersetzt DOI Linker; u. a. BrowZine-Integration erweitert |
| Zotero Web Library | Exportintegration |
| Channels / Record Tab | Überarbeitete Darstellung und Einbettung von Channels in Datensatzansichten |
| Cloudflare Turnstile | Optionale Bot-Abwehr mit Rate Limiter |

Weitere kleinere Verbesserungen betreffen u. a. Checkbox-Facettenzählung, Verbergen fehlender Coverbilder, permanente Links, Kontakt-E-Mail-Darstellung, RSS-Ziele und Anzeige einzelner Felder.

### 6.2 Neu in VuFind 11.1

| Funktion | Beschreibung |
|---|---|
| **Get This** | Konfigurierbare Pop-ups mit Zugriffs-/Beschaffungsoptionen; erheblicher institutionsspezifischer Konfigurationsaufwand möglich |
| **Notices** | Konfigurierbare Hinweise an mehreren UI-Stellen; in 11.1 über YAML-Datei |
| Qualified Dublin Core | Neuer Record Driver |
| Benutzerlesbare Sitemap | YAML-konfigurierbare HTML-Seite; unabhängig von der XML-Sitemap |
| Secondary Blender | Zweites Blended-Search-Backend möglich |
| EBSCO LinkIQ | Neuer Linkresolver-Treiber |
| Tamil | Neue UI-Übersetzung |
| AutowiringFactory | Neue Factory-Option für vereinfachte Service-Erstellung |
| Autocomplete | Berücksichtigt bei Bedarf aktive Filter (`apply_active_filter`) |
| Konfigurierbare UI-Bereiche | `HeaderBar.yaml`, `FooterMenu.yaml` |
| Lokale Solr-Sortdefinitionen | `LocalSortDefinitions` in `searches.ini` |
| Suchbox | `collapseInactiveBackendOptions` zur kompakteren Darstellung mehrerer Backends |
| Facetten | `two_column_facets` in `facets.ini` |
| Bestandsanzeige | `display_no_ils_holdings` zur Steuerung eines Hinweises bei fehlendem Bestand |
| APIs | Authority-Datensätze abrufbar; Website-Volltexte konfigurierbar über `WebApiRecordFields.yaml` |
| YAML-Vererbung | Neues `@parent_config_name` neben `@parent_yaml` |

## 7. Deprecations und Vorbereitung auf VuFind 12

Die folgenden Punkte sind in den **11.x-Quellen** als veraltet, zur Entfernung vorgesehen oder als künftige Migration empfohlen beschrieben. Die Angaben sind als damalige Ankündigungen zu verstehen – die tatsächliche Implementierung in VuFind 12 wurde hier nicht geprüft.

| Betroffener Bereich | Empfohlene Vorbereitung |
|---|---|
| `VuFind\Config\PluginManager` | Umstellung auf `ConfigManager` / `ConfigManagerInterface` |
| `legacyPbkdf2` | Moderne Speicherung verwenden und Altwerte aktualisieren |
| PHP `memcache` | Stattdessen `memcached` einsetzen |
| CAS | Nach Möglichkeit OpenID Connect prüfen |
| Alte Record-Tab-Konfiguration in `module.config.php` | In `recordtabs.ini` überführen |
| ILS-basierte Suche nach Neuerwerbungen (11.1) | Auf Solr-basierte Neuerwerbungssuche wechseln |
| HTML-Attribut `data-submit-on-change` (11.1) | Andere automatische Submit-Logik einsetzen |
| `MyResearchController::DEFAULT_RECOVERY_HASH_LIFE_TIME` | Konstante in `VuFind\Auth\Manager` verwenden |
| View-Helper `Piwik` | Durch `Matomo` ersetzen; Konfigurationsabschnitt beachten |
| LDAP `host` + `port` | Einheitliche `uri`-Angabe verwenden |
| `VuFind\Mailer\Factory` | `VuFind\Mailer\MailerFactory` verwenden |
| Alte OAuth2-Service-Factories | Neue Factory-/Autowiring-Konfiguration prüfen |
| `@parent_yaml` | Perspektivisch `@parent_config_name` prüfen; in 11.1 funktionieren beide |
| Laminas HTTP | Künftige Guzzle-Migration berücksichtigen |

Bereits in 11.0 **entfernte** APIs und Features werden in den jeweiligen Abschnitten berücksichtigt; weitere im Transkript genannte Beispiele umfassen veraltete View-Helper (`AddThis`, `CspNonce`), alte Primo-/SyndeticsPlus-/Booksite-Integrationen, `util/scssBuilder`, alte Solr-Error-Listener und einzelne Record-Driver-/Controller-Methoden. Bei lokalen Anpassungen muss auch nach diesen Namen gesucht werden.

## 8. Praktische Prüfung des eigenen Codes

Die folgende Liste dient als **Suchhilfe**, nicht als automatische Fehlerdiagnose. Ein Treffer kann auch nur ein Kommentar oder kompatibler Altcode sein.

```text
Laminas\Db
Laminas\Config
Laminas\Log
Laminas\Crypt
Laminas\Loader
Laminas\I18n\Translator\TranslatorInterface
VuFind\Db\Row
VuFind\Db\Table
VuFind\Config\PluginManager
VuFind\Config\Locator
getAutoloaderConfig
getConfig(
inlineScript
headLink
headScript
footScript
SimilarItemsCarousel
channel_size
checkboxFilter
Helpers/star-rating.phtml
legacyPbkdf2
recover_hash_lifetime
getDefaultRequestGroup
HoldingsILS
data-submit-on-change
Piwik
@parent_yaml
```

Bei lokal angepassten Klassen zusätzlich deren `__construct`-Methoden, geerbte Methoden und Interfaces mit der Zielversion abgleichen. Die Quellen empfehlen zur Kontrolle der Typkompatibilität auch statische Analyse, beispielsweise mit PHPStan.

## 9. Abnahme- und Testcheckliste

### Vor dem Upgrade

- [ ] Ausgangs- und Zielversion sowie Installationsumgebung dokumentiert
- [ ] Erforderlicher Zwischenschritt über VuFind 10 geklärt
- [ ] PHP-, MySQL-/MariaDB-/PostgreSQL-Versionen überprüft
- [ ] Liste der lokalen Themes, Module, Treiber und Konfigurations-Overrides erstellt
- [ ] Datenbank, Solr, Konfigurationsdateien und Eigenentwicklungen gesichert
- [ ] Wiederherstellung und Wartungsfenster abgestimmt

### Technische Migration

- [ ] Datenbankmigration erfolgreich; Migrationsstatus kontrolliert
- [ ] Alte direkte `laminas-db`-Zugriffe in eigenem Code geprüft
- [ ] Konfigurations-, Logger-, Auth- und HTTP-Integrationen geprüft
- [ ] Composer-Autoloading für eigene Module geprüft
- [ ] Passwortverschlüsselung/`legacyPbkdf2` überprüft und ggf. migriert
- [ ] Bootstrap 5, `AssetManager` und lokale Template-Overrides geprüft
- [ ] Solr-Schema und Konfiguration der Zielversion übernommen
- [ ] Vollständige Neuindexierung erfolgreich durchgeführt
- [ ] `util/update_resource_metadata` ausgeführt bzw. fachlich geprüft
- [ ] JavaScript-/CSS-Builds ohne Grunt-Abhängigkeit möglich (bei 11.1)

### Fachliche Abnahme

- [ ] Einfache und erweiterte Suche, Trefferlisten, Sortierung und Facetten
- [ ] Formaterkennung, Datumsfilter und ähnliche Titel
- [ ] Datensatzdetailseiten, Verfügbarkeit, Bestände und Coverbilder
- [ ] Login, Logout, Passwort-Recovery und ggf. E-Mail-OTP
- [ ] Benutzerkonto, Merkliste, Vormerkung, Ausleihen und Gebühren
- [ ] ILS-Anbindung einschließlich eigener Treibermethoden
- [ ] Lokale Theme-Komponenten, Icons, Navigation, Header und Footer
- [ ] Cookie-Consent, bestehende Einwilligungen und Neuerteilung
- [ ] `SystemStatus`-Zugriff für Monitoring / Zugriffsschutz
- [ ] API-Endpunkte und Integrationen, soweit genutzt
- [ ] Keine relevanten PHP-Fehler, Deprecation-Warnungen oder Solr-Fehler im Log
- [ ] Solr-Performance und Ressourcenverbrauch nach Indexierung überprüft

## 10. Quellen und Einordnung

Diese Dokumentation basiert ausschließlich auf den vier bereitgestellten Texten:

1. **„VuFind 11.0 Released“**, Release-Mitteilung vom **24.11.2025** (`changelogrelease-11.0 VuFind Documentation.md`).
2. **„VuFind 11.1 Released“**, Release-Mitteilung vom **20.07.2026** (`changelogrelease-11.1 VuFind Documentation.md`).
3. **„VuFind 11.0 – What's New? What's Changed?“**, Transkript des Vortrags von Demian Katz (`videosvufind_11.0_-_what_s_new_what_s_changed VuFind Documentation.md`).
4. **„VuFind 11.1 – What's New? What's Changed?“**, Transkript des Vortrags von Demian Katz (`videosvufind_11.1_-_what_s_new_what_s_changed VuFind Documentation.md`).

Die **Release-Mitteilungen** nennen die zentralen Funktionen und Schwerpunkte; die **Video-Transkripte** liefern die technischen Upgrade-Details, API-/Konfigurationsänderungen und Migrationshinweise. Die Prioritäten **P0–P2** und die vorgeschlagene Reihenfolge sind eine aus diesen Quellen abgeleitete praktische Einordnung, keine offizielle VuFind-Priorisierung.

Weiterführende, in den Quellen genannte Anlaufstellen:

- [VuFind – Projekt und Dokumentation](https://vufind.org/)
- [Video: Upgrading VuFind Using Git](https://vufind.org/wiki/videos:upgrading_vufind_using_git)
- [Blogbeitrag zur Aktualisierung lokaler Anpassungen mit Git](http://blog.library.villanova.edu/libtech/2015/07/23/automatically-updating-locally-customized-files-with-git/)

---

**Geltungsbereich:** Überblick und Upgrade-Planung für 11.0/11.1. Eine vollständige Liste sämtlicher geänderter Methoden oder commitgenaue Migrationsanleitung ist durch die vier vorliegenden Texte nicht abgedeckt; dafür sind der Codevergleich mit den tatsächlichen Zielversionen und die dort dokumentierten Upgrade-Werkzeuge erforderlich.
