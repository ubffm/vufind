# Fiddk-Modul – Entwickler-Dokumentation

Diese Dokumentation beschreibt Aufbau, Konfiguration und Erweiterbarkeit des Fiddk-Moduls für VuFind. Das Modul ergänzt VuFind um eigenständige Entitätssuchen und -anzeigen für:
- Personen (SolrPerson)
- Körperschaften (SolrCorporation)
- Ereignisse (SolrEvent)
- Werke (SolrWork)
- sowie EDM-basierte Ressourcen (Solr/EDM)

Dabei kommen eigene Record-Driver, Such-Backends (Options/Params/Results), Controller, View-Helper, Content-Blocks und Verbindungen zu externen Diensten (Lobid, Wikipedia, Wagtail) zum Einsatz.

## Inhalte

1. Überblick
2. Bootstrap & Routing
3. Suche: Backends, Options, Params, Results
4. Record-Driver & EDM-Unterstützung
5. Controller
6. View-Schicht (Formatter, Helper, Templates)
7. Externe Verbindungen (Lobid, Wikipedia, Wagtail)
8. Content Blocks
9. Autocomplete
10. Empfehlungen (Recommendations)
11. Konfiguration
12. Erweiterungshinweise
13. Lizenz

---

## 1. Überblick

Ziel des Moduls ist eine vernetzte Darstellung von Ressourcen und Entitäten einschließlich Anreicherungen (z. B. GND/Wikipedia). Die Implementierung folgt VuFind-Best-Practices via Plugin-Manager und Fabriken.

Zentrale Namespaces/Klassen (Auswahl):
- Record-Driver:
  - Fiddk\RecordDriver\SolrDefault (EDM-Basis)
  - Fiddk\RecordDriver\SolrEdm (EDM-Reader + Traits)
  - Fiddk\RecordDriver\SolrAuthDefault (Authority-Basis)
  - Fiddk\RecordDriver\SolrPerson, \SolrCorporation, \SolrEvent, \SolrWork
- Suche:
  - Fiddk\Search\Options/Params/Results\PluginManager
  - Backends: Fiddk\Search\Factory\SolrPersonBackendFactory, \SolrCorporationBackendFactory, \SolrEventBackendFactory, \SolrWorkBackendFactory
  - Results: Fiddk\Search\Solr\Results, \SolrPerson\Results, \SolrCorporation\Results, \SolrEvent\Results, \SolrWork\Results, \Blender\Results
- Controller:
  - Fiddk\Controller\SearchController (Override), ...SearchController für jede Entität
  - AgentController (Person/Körperschaft), EventController, WorkController
  - DataProviderController, FeedbackController, ShowcaseController
- Views:
  - Fiddk\View\Helper\Fiddk\RecordDataFormatterFactory und -Helper
  - Zusätzliche Helper (LayoutClass, RecordExists, Piwik)
  - Theme-Templates unter themes/fiddk/templates/…
- Verbindungen:
  - Fiddk\Connection\Lobid, \Wikipedia, \Wagtail
- Content-Block:
  - Fiddk\ContentBlock\Statistics (+ Factory)

---

## 2. Bootstrap & Routing

- Bootstrapper: Fiddk\Bootstrapper
  - Implementiert VuFindHttp\HttpServiceAwareInterface.
  - Erzeugt in initViewModel() einen HTTP-Client und setzt `navigation` ins Root-ViewModel (über Fiddk\Connection\Wagtail::getNav()).

- Moduleinbindung: module/Fiddk/Module.php lädt config/module.config.php und ruft den Bootstrapper.

- Routing (module/Fiddk/config/module.config.php):
  - Statische Routen (z. B. `/Showcase` → ShowcaseController::Home, `/DataProvider/[:page]` → DataProviderController::DataProvider).
  - Record-Routen werden via \VuFind\Route\RouteGenerator auf Basis von `$recordRoutes` ergänzt (u. a. solreventrecord, solrpersonrecord, solrcorporationrecord, solrworkrecord).
  - Home-Route `/` → index/Home.

---

## 3. Suche: Backends, Options, Params, Results

- Plugin-Manager Overrides:
  - Options: Fiddk\Search\Options\PluginManager
    - Aliase für solrauthority, solrperson, solrcorporation, solrevent, solrwork.
  - Params: Fiddk\Search\Params\PluginManager
    - Aliase und Mapping auf Fiddk\Search\Solr…\Params.
  - Results: Fiddk\Search\Results\PluginManager
    - Aliase und Fabriken für Fiddk-spezifische Results-Klassen.

- Backend-Fabriken:
  - Fiddk\Search\Factory\SolrPersonBackendFactory (auth; searchConfig/facetConfig: `person`)
  - Fiddk\Search\Factory\SolrCorporationBackendFactory (auth; `corporation`)
  - Fiddk\Search\Factory\SolrEventBackendFactory (auth; `event`)
  - Fiddk\Search\Factory\SolrWorkBackendFactory (default; `work`)
  - Alle registrieren via \VuFind\RecordDriver\PluginManager passende Record-Create-Callbacks (getSolr…Record).

- Options:
  - Fiddk\Search\SolrPerson\Options → getSearchAction(): personsearch-results
  - Fiddk\Search\SolrCorporation\Options → getSearchAction(): corporationsearch-results
  - Fiddk\Search\SolrEvent\Options → getSearchAction(): eventsearch-results
  - Fiddk\Search\SolrWork\Options → getSearchAction(): worksearch-results; getFacetListAction(): work-facetlist
  - Fiddk\Search\SolrAuthority\Options → getRecommendationSettings($handler) lädt bei Person/Corporation/Event/Work spezielle Empfehlungen.

- Params:
  - Fiddk\Search\SolrAuthority\Params: liest id/type/name, setzt Handler (Person/Corporation/Event/Work), überschreibt getDisplayQuery() für GND-Anzeige.
  - Fiddk\Search\SolrPerson/Corporation/Event/Work\Params: Standardverhalten (von \VuFind\Search\Solr\Params).

- Results:
  - Fiddk\Search\Solr\Results → getEntityType(): "Record"
  - Fiddk\Search\SolrPerson/Corporation/Event/Work\Results: setzen backendId und getEntityType() passend.
  - Fiddk\Search\Blender\Results: leitet Entitätstyp aus Suchklasse ab.

---

## 4. Record-Driver & EDM-Unterstützung

- Plugin-Manager: Fiddk\RecordDriver\PluginManager
  - Aliase: solredm, solrperson, solrcorporation, solrevent, solrwork
  - Overrides: \VuFind\RecordDriver\SolrDefault → \Fiddk\RecordDriver\SolrDefault; \VuFind\RecordDriver\SolrAuthDefault → \Fiddk\RecordDriver\SolrAuthDefault
  - Convenience-Getter: getSolrPersonRecord(), getSolrCorporationRecord(), getSolrEventRecord(), getSolrWorkRecord().

- EDM-Basis:
  - SolrDefault: zusätzliche Provider-Hilfen (getInstitutionLinked, getMoreAboutProvider, getInfoAboutProvider, getDProvFromConfig).
  - SolrEdm: nutzt Traits
    - EdmReaderTrait: Lazy-Load von EDM-XML, translateDate/formatToDate etc.
    - EdmBasicTrait: Kern-Getter (Titel-Varianten, Genres, Orte, Datumsangaben, Container, Formate, Events/Works aus Feldern), Jahr, Thumbnails, Links
    - EdmAdvancedTrait: Query-Helfer (isArchiveRecord, askArchive, getKVKLink, queryRecordId, getTitleIfExists, getEventDate)
  - Feature\EdmRecord: Parser für verlinkte/normierte Werte (Namespaces, Labels, Ressourcenauflösungen).

- Authority-Basis:
  - SolrAuthDefault: Heading/UseFor/SeeAlso, Thumbnail, Description sowie GND-/Wikipedia-Anbindung:
    - GND-Details via Fiddk\Connection\Lobid (getGndRecord(), getGndVariants(), getGndIdentifier(), getGndBio(), …)
    - Wikipedia-Metadaten-Auswertung (getPicSource)
    - Zähl- und Abrufmethoden verknüpfter Ressourcen/Werke/Ereignisse (getRelated…Count + getRelated…()).

- Entitäts-Driver:
  - SolrPerson: Beruf/Gender/Orte/Lebensdaten, vielfältige GND-Getter, Related-Counts.
  - SolrCorporation: GND-Getter (Establishment/Termination, PlaceOfBusiness, etc.), Provider-Counts.
  - SolrEvent: Datum/Ort, Deduplicated Authors, Related-Counts.
  - SolrWork: GND-Metadaten (FormOfWork, Composer/Librettist, OpusNum, MediumOfPerformance, …), Ereignis-Relationen.

---

## 5. Controller

- Fiddk\Controller\SearchController:
  - homeAction(): setzt Options, verarbeitet Advanced-Checkbox-Facetten.
  - moreAction(): „Weitere Treffer“-Ansicht.
- Such-Controller:
  - PersonSearchController, CorporationSearchController, EventSearchController, WorkSearchController
  - setzen searchClassId und leiten searchAction() auf resultsAction().
- Record-Controller:
  - AgentController: lädt Person, bei Fehler Fallback auf Körperschaft; Template: RecordDriver/SolrAuthDefault/view
  - EventController, WorkController: setzen gleiches Template.
- Weitere:
  - DataProviderController: statische Seiten mit Sprachsuffix-Handling.
  - FeedbackController: dynamische Formulare (FeedbackForms.yaml), inkl. AskArchive-Vorausfüllung.
  - ShowcaseController: playbillsAction() für Startseiten-Teaser.

---

## 6. View-Schicht (Formatter, Helper, Templates)

- RecordDataFormatterFactory:
  - Registriert Default-Specs für Gruppen: core, ResourceRelated, Person/PersonGnd/PersonRelated, Corporation/…Gnd/…Related, Event/…Gnd/…Related, Work/WorkGnd/WorkRelated, related, Provider, SeeAlso.
  - Bindet u. a. Templates ein:
    - RecordDriver/SolrEdm/data-collapsible.phtml (Sprachsensitives „Mehr/Weniger“),
    - RecordDriver/DefaultRecord/data-related-works.phtml und data-related-events.phtml (zählbasierte Ausgabe mit count()),
    - RecordDriver/DefaultRecord/data-label-array.phtml (sichere Label-Ausgabe).
- View-Helper:
  - Fiddk\LayoutClass: zusätzliche Layout-Optionen (z. B. mainbodyRecord, sidebarRecord).
  - Fiddk\RecordExists (+ Factory): prüft via RecordLoader die Existenz in angegebenen Quellen.
  - Fiddk\Record: z. B. getPublicationDetails().
  - Fiddk\Piwik: Tracking-Code mit Freigabe beliebiger HTML-Attribute im InlineScript.

- Tabs:
  - Fiddk\RecordTab\StaffViewEdm: Staff-View-Tab (Permission: access.StaffViewTab).

---

## 7. Externe Verbindungen

- Fiddk\Connection\Lobid:
  - holt GND-JSON (http://lobid.org/gnd/{gnd}.json), Timeouts und Fehler werden geloggt.
- Fiddk\Connection\Wikipedia:
  - Wikimedia-API (JSON), erhöhter Timeout, Fehlerbehandlung.
- Fiddk\Connection\Wagtail:
  - vorbereitet für Navigationsaufbau (parseJson); Bootstrapper setzt Navigation ins ViewModel (getNav() aktuell als Stub).

---

## 8. Content Blocks

- Fiddk\ContentBlock\Statistics (+ StatisticsFactory):
  - Ermittelt Gesamtzahlen für Solr (Ressourcen), SolrPerson, SolrEvent, SolrWork über Results-PluginManager.
  - In der Konfiguration als contentblock „statistics“ registriert (Alias „Statistics“).

---

## 9. Autocomplete

- Fiddk\Autocomplete\SolrPerson (heading, Backend: SolrPerson),
- Fiddk\Autocomplete\SolrCorporation (heading, SolrCorporation),
- Fiddk\Autocomplete\SolrEvent (heading, SolrEvent),
- Fiddk\Autocomplete\SolrWork (title, SolrWork).

Registriert via vufind.plugin_managers.autocomplete in module.config.php.

---

## 10. Empfehlungen (Recommendations)

- Fiddk\Recommend\AuthorityInfo:
  - nutzt Lobid (optional Wikipedia) zur Anreicherung von Authority-Seiten,
  - formatiert Datumsangaben (formatDisplayDate) und verarbeitet GND vs. Indexdaten (getAuthInfo).

---

## 11. Konfiguration

- Service Manager Overrides (module.config.php):
  - VuFind\RecordDriver\PluginManager → Fiddk\RecordDriver\PluginManager
  - VuFind\Search\Options/Params/Results\PluginManager → Fiddk-Pendants
- vufind.plugin_managers:
  - search_backend: Fabriken für SolrPerson, SolrCorporation, SolrEvent, SolrWork
  - recordtab: Alias „staffviewedm“
  - contentblock: „statistics“
  - recommend: „authorityinfo“
  - autocomplete: Aliase für alle Fiddk-Autocompleter
- Routen: recordRoutes + statische Routen; Home-Route am Ende ergänzt.

Die Options-Klassen erwarten passende INI-Basisnamen: person, corporation, event, work (für searches/facets).

---

## 12. Erweiterungshinweise

Neue Entität hinzufügen (grobe Schritte):
1) Solr-Kern/Backend: Factory analog bestehenden (Search\Factory\Solr…BackendFactory) mit passender `searchConfig`/`facetConfig`.
2) Options/Params/Results: Klassen und PluginManager-Aliase ergänzen; Results mit `backendId` und `getEntityType()`.
3) Record-Driver: Treiber inkl. Traits (bei EDM) und spezifischen Gettern; im RecordDriver\PluginManager alias/factory registrieren.
4) Controller & Routen: Such- und Record-Controller anlegen, in module.config.php registrieren; RouteGenerator erweitern.
5) Views: RecordDataFormatterFactory um neue Spec-Gruppen/Templates erweitern; Templates unter themes/fiddk/… ergänzen.
6) Externe Quellen: Timeouts/Fehlerbehandlung wie bei Lobid/Wikipedia; optionales Caching.

Best Practices:
- In Search-Queries unnötige Features (hl) abschalten, Felder via `fl` begrenzen.
- Übersetzungen/Locale robust prüfen (z. B. substr($lang, 0, 2)).
- HTML-Ausgabe escapen (vgl. data-label-array.phtml).

---

## 13. Lizenz

Das Modul steht unter der GNU General Public License v2. Details siehe Kopfzeilen der Quelldateien.
