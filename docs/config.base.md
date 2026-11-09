# Dokumentation zu `local/config/vufind/config.base.ini`

Diese Datei enthält die aus `local/config/vufind/config.base.ini` extrahierten Kommentare, Beschreibungen und auskommentierten Standard-/Beispieleinstellungen.

> **Hinweis zu lokalen Überschreibungen:** Einige Werte werden in der umgebungsspezifischen `config.ini` gesetzt (z. B. `[Site] url`, `baseUrl`, `email`, `title`, `[Index] url`, `[Mail] default_from`, `[Database]`, `[Logging] file`, `[Security] HMACkey`, `[Feedback] tab_enabled`, `[Matomo] url`, `site_id`).

---

## `[System]`
This section controls global system behavior and can usually be left unmodified.

```ini
; Change to false to take the system offline and show an unavailability message;
; note that you can use the NoILS driver (in [Catalog] section below) to keep VuFind
; up during ILS maintenance.
available       = true
; Change to true to see messages about the behavior of the system as part of the
; output -- only for use when troubleshooting problems. See also the access.DebugMode
; setting in permissions.ini to turn on debug using a GET parameter in the request.
debug           = false
; This setting should be set to false after auto-configuration is complete
autoConfigure = false
; This setting specifies a health check file location. If a health check file exists,
; the getServerStatus AJAX call will return an error regardless of actual status
; allowing the server to be disabled from a load-balancer.
;healthCheckFile = /tmp/disable_vufind
```

## `[Site]`
This section will need to be customized for your installation.

```ini
; Base URL is normally auto-detected, but this setting is used when autodetection is
; not possible (i.e. during sitemap generation at the command line).
; url              = set in config.ini
; baseurl in case of wagtail links
; baseUrl = set in config.ini
; Set to true if VuFind is behind a reverse proxy (typically Apache with mod_proxy),
; make sure your reverse proxy sets the necessary headers.
;reverse_proxy    = true
; Email address to use as the contact point for support requests.
; If you have defined default_from in the [Mail] section, you can set this to an
; empty string to hide the email address from error messages etc. (e.g. when all
; requests should be made via feedback forms).
; email           = set in config.ini
; The title of your site, used in some messages and (by default) page <title> tags.
; Including site title in page titles is recommended to improve browser tab
; navigation for users with screen readers. However, if you wish to remove this from
; titles or change the order/layout, you can customize the title_wrapper translation
; in your language files to remove or relocate the %%siteTitle%% token.
; title            = set in config.ini
; The separator used between page name and site name in the <title> tag of pages.
titleSeparator = "::"
; This is the default theme for non-mobile devices (or all devices if mobile_theme
; is disabled below). Available standard themes:
;   bootstrap5 = HTML5 theme using Bootstrap 5 with minimal styling.
;   sandal5    = An extension of bootstrap5 applying "flat" styling for a more
;                opinionated design.
theme           = fiddk

; Uncomment the following line to use a different default theme for mobile devices.
; You may not wish to use this setting if you are using one of the Bootstrap-based
; standard themes since they support responsive design.
;mobile_theme    = mobile

; Uncomment the following line to use a different theme for Admin module.
;admin_theme     = sandal5

; Automatic asset minification and concatenation setting. When active, the asset
; manager helper will concatenate and minify all viable files to reduce requests
; and load times. This setting is off by default.
;
; This configuration takes the form of a semi-colon separated list of
; environment:configuration pairs where "environment" is a possible APPLICATION_ENV
; value (e.g. 'production' or 'development') or '*'/no prefix for all contexts.
; Possible values for 'configuration' within each environment are 'js', 'css',
; 'off'/false, 'on'/true/'*'. This allows global enabling/disabling of the pipeline
; or separate configurations for different types of resources. Multiple configuration
; values may be comma-separated -- e.g. 'js,css'.
;
; Example: "development:off; production:js,css"
;asset_pipeline = "production:js"

; File size limit for inlining of css @import resources in kilobytes when the asset
; pipeline is enabled (see above) for css files. Set to 0 to disable inlining. Note
; that you will need to delete any css files from local/cache/public directory for
; changes to this setting to take effect.
;
; N.B. The default here is 0 for compatibility with the content security policy.
; A suggested non-zero value is 5, which improves performance by avoiding http
; requests for small images, but any non-zero value requires that data: URIs are
; added as allowed for images in contentsecuritypolicy.ini e.g. with the following
; line:
; img-src[] = "data:"
asset_pipeline_max_css_import_size = 0

; This is a comma-separated list of themes that may be accessed via the ?ui GET
; parameter.  Each entry has two parts: the value used on the URL followed by the
; actual theme name.  For example, http://library.myuniversity.edu/vufind?ui=theme1
; would load the myTheme1 theme with the setting shown below.  Note that the values
; of "standard" and "mobile" are reserved for the default and mobile themes defined
; above.
;alternate_themes = theme1:myTheme1,theme2:myTheme2

; This is a comma-separated list of theme options that will be displayed to the user
; as a drop-down.  Each entry has two parts: a value for the "ui" GET parameter and
; an on-screen description.  "standard" refers to the "theme" setting above, "mobile"
; refers to the "mobile_theme" setting, and all other values must be defined in
; alternate_themes above.  When commented out, no drop-down theme list will display.
;selectable_themes = "standard:Standard Theme,mobile:Mobile Theme"

; Use the browser language setting to set the VuFind language.
browserDetectLanguage = true
language        = de    ; default -- more options available in [Languages] below.
locale          = de_DE
; Comma-separated list of fallback languages to check when a translation is missing
; from the active language. Default fallback languages are the default language above
; and "en". Note that for changes to take effect, the language cache directory may
; need to be cleared.
;fallback_languages = "en-gb,de"

; Set this to specify a default ISO 4217 currency code (used on the fines screen).
; If omitted, the default currency for the locale above will be used.
;defaultCurrency = USD
; Find valid timezone values here:
;   http://www.php.net/manual/en/timezones.php
timezone        = "Europe/Berlin"
; These two settings control how dates and times are formatted in the user interface, using PHP date formatting syntax.
; The default for displayDateFormat is "m-d-Y" (MM-DD-YYYY, e.g. 01-01-2027)
; The default for displayTimeFormat is "H:i" (HH:MM, e.g. 23:01)
;   If you prefer formatting for a 12-hour clock (e.g., 11:01 PM) you can use "g:i A"
; Find details on how to build valid date/time format strings here:
;   https://www.php.net/manual/en/datetime.format.php
displayDateFormat = "m-d-Y"
displayTimeFormat = "H:i"
; The base VuFind URL will load this controller unless the user is logged in:
defaultModule   = Search
; When defaultModule is used, this action will be triggered (default = Home)
defaultAction = Blended
; The base VuFind URL will load this controller when the user is logged in:
defaultLoggedInModule = MyResearch
; When defaultLoggedInModule is used, this action will be triggered (default = Home)
;defaultLoggedInAction = Home
; The search backend that VuFind will use in search boxes when nothing else is
; specified (e.g. on user account pages, search history, etc.). Default = Solr
defaultSearchBackend = Blender
; The route VuFind will send users to following a log out operation. Set to false
; or omit to attempt to retain the user's current context after log out.
;logOutRoute = home
; Default tab to display when a record is viewed (see also RecordTabs.ini):
defaultRecordTab = Similar
; Hide the holdings tab if no holdings are available from the ILS; note that this
; feature requires your ILS driver to support the hasHoldings() method.
hideHoldingsTabWhenEmpty = false
; Whether to load the default tab through AJAX (which brings some performance
; gain but breaks compatibility with non-Javascript-enabled browsers; off by default)
;loadInitialTabWithAjax = true
; The holdingsTemplate to use to display the ILS holdings (defaults to standard).
; See the templates/RecordTab/holdingsils subdirectory of your theme for options.
;holdingsTemplate = extended
; This page will show by default when a user accesses the MyResearch module:
defaultAccountPage = Favorites
; Allow access to the Admin module? (See the access.AdminModule setting in
; permissions.ini for more granular ways to restrict Admin access).
admin_enabled = false
; Show sidebar on the left side instead of right
sidebarOnLeft = true
; Invert the sidebarOnLeft setting for right-to-left languages?
mirrorSidebarInRTL = true
; Put search result thumbnails on the left (true) or right (false)
resultThumbnailsOnLeft = false
; Put favorites list thumbnails on the left (true) or right (false)
listThumbnailsOnLeft = true
; Put hold/checkedout/ILL/etc. item thumbnails on the left (true) or right (false)
accountThumbnailsOnLeft = true
; Show thumbnail on opposite side in right-to-left languages?
mirrorThumbnailsRTL = true
; Handle menu as an offcanvas slider at mobile sizes (in bootstrap5-based themes)
offcanvas = false
; Show (true) / Hide (false) Book Bag - Default is Hide.
showBookBag = false
; Set the maximum amount of items allowed in the Book Bag - Default is 100
bookBagMaxSize = 100
; Show individual add/remove bookbag buttons in search results? (Supersedes cart
; checkboxes and bulk action buttons unless showBulkOptions is true).
bookbagTogglesInSearch = true
; Display bulk items (export, save, etc.) and checkboxes on search result screens?
showBulkOptions = false
; Should users be allowed to save searches in their accounts?
allowSavedSearches = false
; Some VuFind features can be made compatible with non-Javascript browsers at
; a performance cost. By default, this compatibility is disabled, but it can
; be turned on here. Note that even with this setting turned on, some features
; still require Javascript; this simply improves compatibility for certain
; features (such as display of hierarchies).
nonJavascriptSupportEnabled = false
; Generator value to display in an HTML header <meta> tag:
generator = "VuFind 11.1.0"
; The following setting can be set to true in case extended HTML attribute
; escaping is needed. Default is false. Extended escaping is slower and
; results in larger HTML responses than normal escaping, so this setting
; should normally be left disabled, but must be enabled if there are unquoted
; HTML attributes in local templates.
;extendedHtmlAttributeEscaping = false
; Enable or disable a site map page as specified in WCAG 2.2 Technique G63
; (https://www.w3.org/WAI/WCAG22/Techniques/general/G63). Default is false.
; This setting also affects the checkSiteMapPage section plugin check method.
; When enabled the site map page is available at /SiteMap and uses the configuration
; in SiteMap.yaml.
;siteMapPageEnabled = false
```

## `[Session]`
This section allows you to configure the mechanism used for storing user sessions. Available types: `File`, `Memcache`, `Database`, `Redis`. Some of the settings below only apply to specific session handlers; such settings are named with an obvious prefix. Non-prefixed settings are global to all handlers.

```ini
type                        = File
lifetime                    = 3600 ; Session lasts for 1 hour
; Should stored session data be encrypted?
secure = true
; Keep-alive interval in seconds. When set to a positive value, the session is kept
; alive with a JavaScript call as long as a VuFind page is open in the browser.
; Default is 0 (disabled). When keep-alive is enabled, session lifetime above can be
; reduced to e.g. 600.
;keepAlive = 60
;file_save_path              = /tmp/vufind_sessions
;memcache_host               = localhost
;memcache_port               = 11211
;memcache_connection_timeout = 1
; The name of the PHP client library to use for connecting to Memcache (can be either
; "Memcache" or "Memcached"); default is "Memcache". Note that if you change from one
; library to another, you should flush your cache to avoid problems caused by
; inconsistencies in data encoding between the two libraries.
;memcache_client             = Memcache
;
; Settings related to Redis-based sessions; default values are listed below
;redis_host               = localhost
;redis_port               = 6379
;redis_connection_timeout = 0.5
;redis_db                 = 0
;redis_user               = username (optional)
;redis_auth               = some_secret_password
;redis_version            = 3
;redis_standalone         = true
```

## `[Cookies]`
This section controls how VuFind creates cookies (to store session IDs, bookbag contents, theme/language settings, etc.).

```ini
; In case there are multiple VuFind instances on the same server and they should not
; share cookies/sessions, this option can be enabled to limit the session to the
; current path. Default is false, which will place cookies at the root directory.
;limit_by_path = true
; If VuFind is only accessed via HTTPS, this setting can be enabled to disallow
; the browser from ever sending cookies over an unencrypted connection (i.e.
; before being redirected to HTTPS). Default is false.
;only_secure = true
; Whether to set cookies set by the server (apart from cart function) "HTTP only" so
; that they cannot be accessed by scripts. Default is true.
;http_only = false
; Set the domain used for cookies (sometimes useful for sharing the cookies across
; subdomains); by default, cookies will be restricted to the current hostname.
;domain = ".example.edu"
; This sets the session cookie's name. Comment this out to use the default
; PHP_SESS_ID value. If running multiple versions of VuFind (or multiple PHP
; applications) on the same host, it is strongly recommended to give each a
; different session_name setting to avoid data contamination.
session_name = VUFIND_SESSION
; Set the SameSite attribute used for cookies. Default is Lax. See e.g.
; https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite for
; more information
;sameSite = "Lax"
; Whether to ask for cookie consent (required e.g. for GDPR compliance). Default is
; false. See also CookieConsent.yaml for cookie categories and other settings.
;consent = false
; Categories to enable for cookie consent. This is a comma-separated list of
; categories defined in CookieConsent.yaml. Default is "essential".
;consentCategories = "essential,matomo"
; Cookie consent revision. If you change the categories or do any other major changes
; in CookieConsent.yaml, you will need to increase revision as well so that users get
; prompted for consent.
;consentRevision = 0
```

## `[Catalog]`
Please set the ILS that VuFind will interact with.

Available drivers:
- `Aleph`
- `Alma`
- `Amicus`
- `ComposedDriver` (combining multiple drivers)
- `DAIA` (using either XML or JSON API)
- `Demo` (fake ILS driver returning complex responses)
- `Evergreen`
- `Folio`
- `GeniePlus`
- `Horizon` (basic database access only)
- `HorizonXMLAPI` (more features via API)
- `Innovative` (for INNOPAC; see also `SierraRest`)
- `Koha` (basic database access only)
- `KohaILSDI` (more features via ILS-DI API)
- `KohaRest` (the most feature-complete Koha driver using Koha's REST API. Requires at least Koha 20.05 and the `koha-plugin-rest-di` extension found at: https://github.com/natlibfi/koha-plugin-rest-di)
- `MultiBackend` (to chain together multiple drivers in a consortial setting)
- `NewGenLib`
- `NoILS` (for users with no ILS, or to disable ILS features during maintenance)
- `PAIA`
- `Polaris`
- `Sample` (fake ILS driver returning bare-minimum data)
- `SierraRest` (accesses Sierra via REST API)
- `Symphony` (uses native SirsiDynix APIs)
- `Unicorn` (also applies to Symphony; requires installation of connector found at: http://code.google.com/p/vufind-unicorn/)
- `Virtua`
- `Voyager` (database access only; for Voyager 6+)
- `VoyagerRestful` (for Voyager 7+ w/ RESTful web services)
- `XCNCIP2` (for XC NCIP Toolkit v2.x)

If you haven't set up your ILS yet, two fake drivers are available for testing purposes. `Sample` is fast but does very little; `Demo` simulates more functionality of a real ILS but may slow down your system by performing extra searches. If you don't plan to use an ILS, the `NoILS` driver is your best option.

Note: Enabling most of the features in this section will only work if you use an ILS driver that supports them; not all drivers support holds/renewals.

```ini
driver          = NoILS

; If true, the user will be presented with a login form for entering ILS credentials
; when attempting to access ILS functionality without working cached credentials.
; If false, the user will instead see an error screen. You should use the false value
; here when the user's ILS credentials are automatically loaded into the database via
; [Authentication] and related configuration, and you never expect a user to manually
; provide these details.
;
; Note: if you disable user login, this can also have implications for the
; library_cards setting (see below).
allowUserLogin = false

; If true (default), any ILS credentials stored with the user are checked up-front
; when the user logs in to VuFind. If the credentials don't work, they're cleared,
; and the user will be prompted for new credentials when accessing a page that
; needs them. This setting is ignored if allowUserLogin is false.
checkILSCredentialsOnLogin = true

; ILS data cache life time in seconds. The cache is used to avoid repeating
; requests too often (but often enough to not use stale data).
; Default is 60 seconds. Set to 0 to disable caching.
; * can be used to set the default for all cacheable functions (patronLogin,
; getProxiedUsers, getProxyingUsers or getPurchaseHistory):
;cacheLifeTime[*] = 60
; Default cache life time can be overridden for any cacheable function:
;cacheLifeTime[getProxiedUsers] = 300

; loadNoILSOnFailure - Whether or not to load the NoILS driver if the main driver fails
loadNoILSOnFailure = false

; healthCheckId - When performing ILS health checks (see the IlsStatusMonitor block
; described in searches.ini for more details), VuFind will look up the status of a
; bibliographic record and ensure the response is well-formed. By default, it uses
; a bibliographic record ID of "1". If your ILS does not accept "1" as a valid
; identifier, you can specify a different health check record ID here.
;healthCheckId = 12345

; List of search backends that contain records from your ILS (defaults to Solr
; unless set otherwise). You can set ilsBackends = false to disable ILS status
; loading entirely.
;ilsBackends[] = Solr

; This setting determines how and when hold / recall links are displayed.
; Legal values:
; - all (Show links for all items - Place Hold for Available Items and Place Recall
;   for unavailable items)
; - availability (Only show recall links if ALL items on bib are currently
;   unavailable)
; - disabled (Never show hold/recall links)
; - driver (Use ILS driver to determine which items may be held/recalled; best option
;   if available, but not supported by all drivers)
; - holds (Only show links for available items)
; - recalls (Only show links for unavailable items)
; default is "all"
holds_mode = "all"

; Set this to true if you want to allow your ILS driver to override your holds_mode
; setting on a record-by-record basis; this may be useful for local customizations,
; but in most cases you should leave this setting unchanged.  Overrides are ignored
; for mode settings of "driver" or "disabled."
allow_holds_override = false

; Determines if holds can be cancelled or not. Options are true or false.
; default is false
cancel_holds_enabled = false

; Determines if storage retrieval requests can be cancelled or not.
; Options are true or false.
; default is false
cancel_storage_retrieval_requests_enabled = false

; Determines if ILL requests can be cancelled or not.
; Options are true or false.
; default is false
cancel_ill_requests_enabled = false

; Determines if item can be renewed or not. Options are true or false.
; default is false
renewals_enabled = false

; Determines if title level holds are displayed or not.
; Legal values:
; - disabled (Never show title Holds - Default)
; - always (Always show title Holds)
; - availability (Only show title holds if ALL items on bib are currently
;   unavailable)
; - driver (Use ILS driver to determine which items may be held/recalled; best option
;   if available, but not supported by all drivers)
title_level_holds_mode = "disabled"

; Determines how holdings are grouped in the record display, using fields from
; the item information provided by the ILS driver.
;
; Most commonly-used values:
; - holdings_id,location (Use holdings record id if available, location name as
;   secondary - Default)
; - location (Use location name)
;
; See https://vufind.org/wiki/development:plugins:ils_drivers#getholding for
; more options (though not every ILS driver supports every possible value).
;
; Note that there may also be driver-specific values outside of the specification,
; such as:
; - item_agency_id (XCNCIP2 driver's Agency ID, which may be useful in consortial
;   environments)
;
; You may use multiple group keys (delimited by comma), e.g.,
; - item_agency_id,location
;holdings_grouping = holdings_id,location

; Text fields such as holdings_notes gathered from items to be displayed in each
; holdings group in the display order.
; The default list is 'holdings_notes', 'summary', 'supplements' and 'indexes'. The
; deprecated field 'notes' is used as an alias for 'holdings_notes'.
; Note that displayed information depends on what the ILS driver returns.
;holdings_text_fields[] = 'holdings_notes'
;holdings_text_fields[] = 'summary'

; Whether support for multiple library cards is enabled. Default is false.
;
; Note: to create library cards through a manual form, users will require
; the allowUserLogin setting (see above) to be enabled as well.
;library_cards = true

; Whether support for connecting multiple library cards via authentication is
; enabled. This is currently only supported for Shibboleth. Default is false.
; See https://vufind.org/wiki/configuration:shibboleth:library_cards for details.
;auth_based_library_cards = true

; The number of checked out items to display per page; 0 for no limit (may cause
; memory problems for users with huge numbers of items). Default = 50.
;checked_out_page_size = 50

; The number of historic loans to display per page; 0 for no limit (may cause
; memory problems for users with a large number of historic loans). Default = 50
;historic_loan_page_size = 50

; Whether to display the item barcode for each loan. Default is false.
;display_checked_out_item_barcode = true

; Whether to display items without barcodes in the Holdings tab. Prior to VuFind
; 9.0, hiding items without barcodes was VuFind's default behavior. The current
; default value is true -- to always display ALL items.
; If you need to apply more complex filtering rules to Holdings display, you
; can extend/override the VuFind\View\Helper\Root\Holdings::holdingIsVisible method.
;display_items_without_barcodes = true

; Whether to display the loan type on items in the Holdings display (if supported
; by driver; default = false).
;display_loan_type_in_holdings = true

; Whether to display a warning box when no ILS holdings are available. Use "always"
; to display the warning even when online holdings (e.g., full-text URLs) are available.
; Use "not_online" to display the warning only when no online resources are available.
; Leave this setting commented out to disable the warning entirely. The message in the
; warning box can be translated with the "no_holdings_found" translation key in the
; language files.
;display_no_ils_holdings_warning = "always"
```

## `[Account]`
This section controls features related to user accounts.

```ini
; Allow the user to set a home library through the Profile screen, which will
; override ILS-provided default pickup locations throughout the system.
set_home_library = true

; Allow the user to "subscribe" to search history entries in order to receive
; email notifications of new search results.
schedule_searches = false

; Should we always send a scheduled search email the first time we run notices
; after a user has subscribed (true), or should we only send an email when there
; is actually something new (false, default)
force_first_scheduled_email = false

; When schedule_searches is set to true, you can customize the schedule frequencies
; here -- just use the number of days between notifications in the brackets. Labels
; will be run through the translator.
;scheduled_search_frequencies[0] = schedule_none
;scheduled_search_frequencies[1] = schedule_daily
;scheduled_search_frequencies[7] = schedule_weekly
```

## `[Authentication]`
This section allows you to determine how the users will authenticate.

You can authenticate using one or more of the following methods:
- `AlmaDatabase` (combination of VuFind database and Alma account; see also `Alma.ini`)
- `CAS` (see also `[CAS]` section -- deprecated, use `OpenIDConnect` instead when possible)
- `Database`: VuFind's internal user database
- `Email` (see notes below)
- `Facebook` (see also `[Facebook]` section)
- `ILS`: the local ILS
- `LDAP`: an LDAP directory (see also `[LDAP]` section)
- `MultiILS`: multiple ILSes (see also `MultiBackend.ini`)
- `OpenIDConnect`: Open ID Connect (see also `OpenIDConnectClient.ini`)
- `PasswordAccess`: a hard-coded list of access passwords (see also `[PasswordAccess]` section)
- `Shibboleth` (see also `[Shibboleth]` section)
- `SimulatedSSO`: simulated single sign-on for testing/development (see `SimulatedSSO.ini`)
- `SIP2` (see also `[SIP2]` section)
- some combination of the above (via the `MultiAuth` or `ChoiceAuth` options).

The `Email` method is special; it is intended to be used through `ChoiceAuth` in combination with `Database` authentication (or any other method that reliably stores the user's email address) to make it possible to log in by receiving an authentication link at the email address stored in VuFind's database. `Email` is also supported as the primary authentication mechanism for some ILS drivers (e.g. Alma). In these cases, `ChoiceAuth` is not needed, and `ILS` should be configured as the Authentication method; see the ILS driver's configuration for possible options.

Also note that the `Email` method stores hashes in your database's `auth_hash` table. You should run the `php $VUFIND_HOME/public/index.php util expire_auth_hashes` utility periodically to clean out old data in this table.

```ini
method = Database

; This setting only applies when method is set to ILS.  It determines which
; field of the ILS driver's patronLogin() return array is used as the username
; in VuFind's user database.  If commented out, it defaults to cat_username
; (the recommended setting in most situations).
;ILS_username_field = cat_username

; Whether or not to hide the Login Options; note that even when this is set to
; false, ILS driver settings may be used to conditionally hide the login. See
; hideLogin in the [Settings] section of NoILS.ini for an example.
hideLogin = true

; When set to true, uses AJAX calls to annotate the account menu with
; notifications (overdue items, total fines, etc.)
enableAjax = true

; When set to true, replicates the account menu as a drop-down next to the
; account link in the header.
enableDropdown = false

; Setting this to false will turn off password hashing and store user passwords
; in plain text, which is NOT RECOMMENDED, but may be useful if you are in the
; process of migrating data from a very old VuFind release that predates password
; hashing. DO NOT CHANGE under other circumstances. Also note that this setting
; only applies when method = Database or AlmaDatabase above.
hash_passwords = true

; Allow users to recover passwords via email (if supported by Auth method)
; You can set the subject of recovery emails in your
; language files under the term "recovery_email_subject"
recover_password = false
; Time (seconds) before another recovery attempt can be made
recover_interval      = 60
; Length of time (seconds) before a recovery hash can no longer be used (expires)
; Default: One hour
recover_hash_lifetime = 1209600
; Tell user the truth whether the account could be found by the login/email or not
; Setting recover_be_honest to true is a security risk, because attackers
; can check if certain users have a login or probe on valid emails
recover_be_honest = false

; Maximum number of verification attempts using a one-time password sent by email. Default is 3.
;otp_max_attempts = 3

; Allow users to set change their email address (if supported by Auth method).
; When turning this on, it is also strongly recommended to turn on verify_email
; below.
change_email = false

; Allow users to set change their passwords (if supported by Auth method)
change_password = true

; Force users to verify their email address before being able to log in
; (only if method=Database) or make changes to it (if change_email=true).
; If you wish to customize the email messages used by the system, see the
; translation strings starting with verify and change_notification, as well as
; the notify-email-change.phtml and verify-email.phtml Email templates.
verify_email = false

; Set this to false if you would like to store catalog passwords in plain text
encrypt_ils_password = true

; This is the key used to encrypt and decrypt catalog passwords.  This must be
; filled in with a random string value when encrypt_ils_passwords is set to true.
; Note: aes requires a key exactly 32 characters long.
; 32 chars ->        "--------------------------------"
ils_encryption_key = false

; This is the algorithm used to encrypt and decrypt catalog passwords.
; A symmetrical encryption algorithm must be used.
; You can use openssl_get_cipher_methods() to see available options on your system.
; Common choices: blowfish (deprecated -- do not use), aes (recommended), cast, sm4
; If you want to convert from one algorithm to another, run this from $VUFIND_HOME:
;   php public/index.php util switch_db_hash new_algorithm new_key
ils_encryption_algo = "aes"

; This setting may optionally be uncommented to restrict the email domain(s) from
; which users are allowed to register when using the Database or AlmaDatabase method.
;legal_domains[] = "myuniversity.edu"
;legal_domains[] = "mail.myuniversity.edu"

; Specify default minimum and maximum new username length (Auth method may override
; this).
;minimum_username_length = 3
;maximum_username_length = 32
; Specify default limit of accepted characters in the username. Allowed values
; are "numeric", "alphanumeric" or a regular expression.
; The following default requires the username to consist of printable characters
; allowed in email addresses (i.e. !#$%&'*+-/=?^_`{|}~) and letters and decimal
; numbers in any script (see
; https://www.php.net/manual/en/regexp.reference.unicode.php for more information):
username_pattern = "([\\x21\\x23-\\x2B\\x2D-\\x2F\\x3D\\x3F\\x40\\x5E-\\x60\\x7B-\\x7E\\p{L}\\p{Nd}]+)"
; Specify default hint about what the username may contain when using a regexp
; pattern. May be text or a translation key. The "numeric" and "alphanumeric"
; patterns have translated default hints.
username_hint = username_only_letters_numbers_and_basic_punctuation

; Specify default minimum and maximum password length (Auth method may override
; this).
;minimum_password_length = 4
;maximum_password_length = 32
; Specify default limit of accepted characters in the password. Allowed values
; are "numeric", "alphanumeric" or a regular expression
;password_pattern = "(?=.*\d)(?=.*[a-z])(?=.*[A-Z])"
; Specify default hint about what the password may contain when using a regexp
; pattern. May be text or a translation key. The "numeric" and "alphanumeric"
; patterns have translated default hints.
;password_hint = "Include both upper and lowercase letters and at least one number."

; Uncomment this line to switch on "privacy mode" in which no user information
; will be stored in the database. Note that this is incompatible with social
; features, password resets, and many other features. It is not recommended for
; use with "Database" or "AlmaDatabase" authentication, since the user will be
; forced to create a new account upon every login.
;privacy = true

; Allow a user to delete their account. Default is false.
;account_deletion = true
; Whether comments added by a user are deleted when they remove their account.
; Default is true.
;delete_comments_with_user = false
; Whether ratings added by a user are deleted when they remove their account.
; Default is true.
;delete_ratings_with_user = false

; "Remember me" functionality for listed login methods. If the user chooses to save
; the login, a login token cookie and a database entry will be created.
; The implementation is based on Improved Persistent Login Cookie Best Practice, see
; https://gist.github.com/oleg-andreyev/9dcef18ca3687e12a071648c1abff782
; User's browser information is stored with the login token. This requires an up to
; date browscap cache. The browscap cache can be updated in the Maintenance section
; of the admin UI or by using the util/browscap command line utility. If you use the
; command line utility, you will need to ensure that the user of the web server has
; read and write access to the cache directory and its contents as well.
; If you use cookie consent, ensure that the loginToken cookie is included (look for
; {{vufind_login_token_cookie_name}} in CookieConsent.yaml).
;persistent_login = "database,multiils"

; Persistent login token lifetime in days
;persistent_login_lifetime = 60

; Whether to send email warnings about suspicious logins. Default is true.
;send_login_warnings = true

; Whether to use a more lenient token rotation to avoid cookie issues with Safari,
; which sometimes fails to maintain cookies across sessions. Default is true.
;lenient_token_rotation = true

; Subject for the email sent after a suspicious login has been detected.
; Default email subject will be the translation for
; "persistent_login_warning_email_subject" and any custom value used here
; will also be run through the translator.
;persistent_login_warning_email_subject = "%%title%%: Invalid login attempt detected"
```

### Optionale Authentifizierungs-Sektionen

```ini
; See the comments in library/VF/Auth/MultiAuth.php for full details
; on using multiple authentication methods.  Note that MultiAuth assumes login
; with username and password, so some methods (i.e. Shibboleth) may not be
; compatible.
;[MultiAuth]
;method_order   = ILS,LDAP
;filters = "username:trim,password:trim"

; Present two auth options on the login screen. Each choice given must also be
; configured in its relevant section. (The code should allow for more than 2
; choices, but styling would need to be expanded / modified)
;
; WARNING! This module does not account for the possibility that the auth
; choices you present may return different usernames. You would want a user to
; be able to log in via any method and see the same account. To make sure that
; is the case, you should ensure that the usernames given by the authentication
; methods themselves are the same for any given user.
;[ChoiceAuth]
;choice_order = Shibboleth,Database

; Reduce access to a set of single passwords
; This is only used when Authentication method is PasswordAccess. See above.
; Recommended to be used in conjunction with very restricted permissions.ini settings
; and with most social settings disabled
;[PasswordAccess]
; access_user is a map of users to passwords
; entering a correct password will login as that user
;access_user[user] = password
;access_user[admin] = superpassword
; For higher security create hashes using
; php -r 'echo password_hash("<password>", PASSWORD_DEFAULT) . PHP_EOL;'
; and add it to the following option instead.
;access_user_hashed[user] =

; LDAP is optional.  This section only needs to exist if the
; Authentication Method is set to LDAP.  When LDAP is active,
; uri, basedn and username are required.
; Using host and port instead of uri is deprecated.
;[LDAP]
;uri             = ldap://ldap.myuniversity.edu:389
;host            = ldap.myuniversity.edu
;port            = 389       ; LDAPS usually uses port 636 instead
; By default, when you use regular LDAP (not LDAPS), VuFind uses TLS security.
; You can set disable_tls to true to bypass TLS if your server does not support
; it. Note that this setting is ignored if you use ldaps:// in the host setting.
;disable_tls     = false
;basedn          = "o=myuniversity.edu"
;username        = uid
; separator string for mapping multi-valued ldap-fields to a user attribute
; if no separator is given, only the first value is mapped to the given attribute
;separator = ';'
; Optional settings to map fields in your LDAP schema to fields in the user table
; in VuFind's database -- the more you fill in, the more data will be imported
; from LDAP into VuFind:
;firstname       = givenname
;lastname        = sn
;email           = mail
;cat_username    =
;cat_password    =
;college         = studentcollege
;major           = studentmajor
; If you need to bind to LDAP with a particular account before
; it can be searched, you can enter the necessary credentials
; here.  If this extra security measure is not needed, leave
; these settings commented out.
;bind_username   = "uid=username o=myuniversity.edu"
;bind_password   = password

; SIP2 is optional.  This section only needs to exist if the
; Authentication Method is set to SIP2.
;[SIP2]
;host            = ils.myuniversity.edu
;port            = 6002

; Shibboleth is optional.  This section only needs to exist if the
; Authentication Method is set to Shibboleth. Be sure to set up authorization
; logic in the permissions.ini file to filter users by Shibboleth attributes.
;[Shibboleth]
; Server param with the identity provider entityID if a Shibboleth session exists.
; If omitted, Shib-Identity-Provider is used.
;idpserverparam = Shib-Identity-Provider
; Optional: Session ID parameter for SAML2 single logout support. If omitted, single
; logout support is disabled. Note that if SLO support is enabled, Shibboleth session
; ID's are tracked in external_session table which may need to be cleaned up with the
; util/expire_external_sessions command line utility. See
; https://vufind.org/wiki/configuration:shibboleth for more information on how
; to configure the single logout support.
;session_id = Shib-Session-ID
; Check for expired session - user is automatically logged out when Shibboleth
; session is not present (default = true if unset); note that expiration check
; also requires session_id to be set above.
;checkExpiredSession = false
; Optional: you may set attribute names and values to be used as a filter;
; users will only be logged into VuFind if they match these filters.
;userattribute_1       = entitlement
;userattribute_value_1 = urn:mace:dir:entitlement:common-lib-terms
;userattribute_2       = unscoped-affiliation
;userattribute_value_2 = member
; Set to true when shibboleth attributes must be read from headers instead of
; environment variables - for example if you use proxy server and shibboleth is
; running on proxy side. In that case you should protect against header spoofing:
; see https://wiki.shibboleth.net/confluence/display/SP3/SpoofChecking for details
;use_headers           = false
; Required: the attribute Shibboleth uses to uniquely identify users.
;username              = persistent-id
; Required: Shibboleth login URL.
;login                 = https://shib.myuniversity.edu/Shibboleth.sso/Login
; Optional: Shibboleth logout URL.
;logout                = https://shib.myuniversity.edu/Shibboleth.sso/Logout
; Optional: URL to forward to after Shibboleth login (if omitted,
; defaultLoggedInModule from [Site] section will be used).
;target                = https://shib.myuniversity.edu/vufind/MyResearch/Home
; Optional: provider_id (entityId) parameter to pass along to Shibboleth login.
;provider_id           = https://idp.example.edu/shibboleth-idp
; Some or all of the following entries may be uncommented to map Shibboleth
; attributes to user database columns:
;cat_username = HTTP_ALEPH_ID
;cat_password = HTTP_CAT_PASSWORD
;email = HTTP_MAIL
;firstname = HTTP_FIRST_NAME
;lastname = HTTP_LAST_NAME
;college = HTTP_COLLEGE
;major = HTTP_MAJOR
;home_library = HTTP_HOME_LIBRARY
; Enable if you want to override mapping or required attributes for specific IdP
; in Shibboleth.ini
;allow_configuration_override = true

; CAS is optional (and deprecated). This section only needs to exist if the
; Authentication Method is set to CAS.
;[CAS]
; Optional: the attribute CAS uses to uniquely identify users. (Omit to use
; native CAS username instead of an attribute-based value).
;username              = uid
; Required: CAS Hostname.
;server                = cas.myuniversity.edu
; Required: CAS port.
;port                 = 443
; Required: CAS context.
;context                 = /cas
; Required: CAS Certificate Path. (Set to false to bypass authentication;
; BYPASSING AUTHENTICATION IS *NOT* RECOMMENDED IN PRODUCTION).
;CACert = /etc/pki/cert/cert.crt
; Required: CAS login URL.
;login                 = https://cas.myuniversity.edu/cas/login
; Required: CAS logout URL.
;logout                = https://cas.myuniversity.edu/cas/logout
; Optional : CAS client base URL. If omitted, [Site][url] will be used
;service_base_url[]    = "https://vufind.myuniversity.edu"
;service_base_url[]    = "http://vufind.myuniversity.edu"
; Optional: CAS logging, forwarded to [Logging]
;debug                 = false
; Optional: URL to forward to after CAS login (if omitted,
; defaultLoggedInModule from [Site] section will be used).
;target                = http://lib.myuniversity.edu/vufind/MyResearch/Home
; Optional: protocol to follow (legal values include CAS_VERSION_1_0,
; CAS_VERSION_2_0, CAS_VERSION_3_0 and SAML_VERSION_1_1; default is
; SAML_VERSION_1_1)
;protocol = SAML_VERSION_1_1
; Some or all of the following entries may be uncommented to map CAS
; attributes to user database columns:
;cat_username = acctSyncUserID
;cat_password = catPassword
;email = mail
;firstname = givenName
;lastname = sn
;college = college
;major = major1
;home_library = library

; Facebook may be used for authentication; fill in this section in addition to
; turning it on in [Authentication] above to use it. You must register your
; VuFind instance as an application at http://developers.facebook.com to obtain
; credentials.
;[Facebook]
;appId = "your app ID"
;secret = "your app secret"
```

## `[Index]` & `[IndexHttp]`
This section defines the location/behavior of the Solr index and requires no changes for most installations.

```ini
[Index]
; url can also be an array of servers. If so, VuFind will try the servers one by one
; until one can be reached. This is only useful for advanced fault-tolerant Solr
; installations.
; url             = set in config.ini
; Default bibliographic record index name (core or collection)
default_core    = biblio
; Default authority record index name (core or collection)
default_authority_core = authority
; This setting needs to match the <maxBooleanClauses> setting in your solrconfig.xml
; file; when VuFind has to look up large numbers of records using ID values, it may
; have to restrict the size of its result set based on this limitation.
maxBooleanClauses = 1024
; This is the timeout in seconds when communicating with the Solr server.
timeout = 30
; This is the Dismax handler to use if nothing is specified in searchspecs.yaml.
; You can choose dismax for standard Dismax (the default) or edismax for Extended
; Dismax, or you can configure your own custom handler in solrconfig.xml.
default_dismax_handler = dismax
; This is the number of records to retrieve in a batch e.g. when building a record
; hierarchy. A higher number results in fewer round-trips but may increase Solr's
; memory usage. Default is 1000. Set to false to disable use of cursors in retrieval
; (which is only necessary if your local custom code leverages Solr features which
; are incompatible with cursorMark usage).
;cursor_batch_size = 1000
; This limits the number of records to retrieve in a batch e.g. when retrieving
; records for a list. Default is 100 which should be a good number to avoid
; memory problems.
;record_batch_size = 100

; HTTP settings used when making a request to the search index. These can be used
; to add new settings or override the defaults from [Http] section.
; See [Http] section for more information about the settings.
[IndexHttp]
;sslcapath = "/etc/ssl/certs" ; e.g. for Debian systems
;sslcafile = "/etc/pki/tls/cert.pem" ; e.g. for CentOS systems
```

## `[Reserves]`
Enable/Disable searching reserves using the "reserves" Solr index. When enabling this feature, you need to run the `util/index_reserves.php` script to populate the new index.

```ini
search_enabled  = false
```

## `[Mail]`
This section requires no changes for most installations; if your SMTP server requires authentication, you can fill in a username and password below.

```ini
; For normal SMTP without advanced settings (like disabled certificate verification)
; you can use the following settings:
host            = localhost
port            = 25
;username       = user
;password       = pass
; Use password_file to load the secret from another file instead of including it
; directly in this configuration.
;password_file = /path/to/secret
; Set to true to use the SMTPS protocol, or false to use SMTP (the default).
; Regardless of this setting, TLS will be used when available.
;secure         = true
; This setting enforces a limit (in seconds) on the lifetime of an SMTP
; connection before the connection is checked, which can be useful when sending
; batches of emails, since it can help avoid errors caused by server timeouts.
; This is mapped to the ping_threshold option in Symfony Mailer.
; Comment out the setting to use the default ping threshold.
; See https://symfony.com/doc/current/mailer.html#other-options for more
; information.
connection_time_limit = 60
; The server name to report to the upstream mail server when sending mail.
;name = vufind.myuniversity.edu

; To use SMTP with special settings or any other transport than SMTP,
; you can use the dsn setting to define the connection string.
; See https://symfony.com/doc/current/mailer.html#transport-setup for more
; information on the DSN configuration. Also note that VuFind does not include the
; third-party transports out of box, but you can add them to composer.local.json.
;dsn             = "native://default"
; Use this setting for normal SMTP without certificate verification:
;dsn             = "smtp://localhost:25?verify_peer=0"

; Uncomment this setting to disable outbound mail but simulate success; this
; is useful for interface testing but should never be used in production!
;testOnly = true
; Set to a file path writable by VuFind to log email messages into that file;
; primarily intended for testing/debugging purposes.
;message_log = /tmp/emails.log
; Message log format. Valid options are 'plain' for plain text (default) and
; 'serialized' for serialized Email objects.
;message_log_format = plain
; The action to email records and searches may be "enabled", "disabled" or "require_login"
; (default = "require_login")
; If set to "enabled", users can send anonymous emails; If set to "require_login",
; they must log in first; "disabled" disables the action completely
email_action = require_login
; Should we put the logged-in user's address in the "from" field by default?
user_email_in_from = false
; Should we put the logged-in user's address in the "to" field by default?
user_email_in_to = false
; Should the user be allowed to edit email subject lines?
user_editable_subjects = false
; How many recipients is the user allowed to specify? (use 0 for no limit)
maximum_recipients = 3
; Populate the "from" field with this value if user_email_in_from is false and/or no
; user is logged in. If specified, this is also used as the "from" address for any
; messages sent by the system (such as password change notifications).
;default_from = "no-reply@myuniversity.edu"
; Should we hide the "from" field in email forms? If no from field is visible, emails
; will be sent based on user_email_in_from and default_from above, with the email
; setting from the [Site] section used as a last resort.
disable_from = true
; From field override. Setting this allows keeping the "from" field in email forms
; but will only use it as a reply-to address. The address defined here is used as the
; actual "from" address.
; Note: If a feature explicitly sets a different reply-to address (for example,
; Feedback forms), the original from address will NOT override that reply-to value.
;override_from = "no-reply@myuniversity.edu"

; Being a special case of mail message, sending record results via SMS ("Text this")
; may be "enabled" or "disabled" ("enabled" by default).
; Should you choose to leave it enabled, see also sms.ini for further
; configuration options.
sms = disabled

; Set this value to "database" to shorten links sent via email/SMS and
; store its path in the database (default "none").
url_shortener = none

; Which method to use for generating the short link key. Options:
; - base62: Base62-encode the database row ID (insecure, but makes very short URLs)
; - md5: Create a salted MD5 hash of the URL (DEFAULT: more private, but also longer)
; ...or any hash algorithm supported by PHP's hash() function.
url_shortener_key_type = md5
; default_from     = "set in config.ini"

; Which redirect mechanism to use when shortlinks are resolved.
; threshold:1000 (default) : If the URL is shorter than the given size, HTTP is used, else HTML.
; html                     : HTML meta redirect after 3 seconds with infobox.
; http                     : Use HTTP location header for redirects.
;                            May cause problems if the HTTP header is longer than 8192 bytes
;                            due to long URL's.
;url_shortener_redirect_method = threshold:1000
```

## `[Database]`
This section needs to be changed to match your database connection information.

```ini
; Connection string format is [platform]://[username]:[password]@[host]:[port]/[db]
; where:
; [platform/driver] = database platform (mysql, mariadb, oci8 or pgsql)
; [username] = username for connection
; [password] = password for connection (optional)
; [host] = host of database server
; [port] = port of database server (optional)
; [db] = database name
; For more granular configuration, comment out the database setting and use the individual parameters below.
; ******************** set in config.ini! ******************************
;database          = mysql://root@localhost/vufind
;database_driver = "mysql"
;database_username = "notroot"
;database_password = "password"
; database_password_file will be used in priority over database_password
;database_password_file = "/path/to/secret"
;database_host = "localhost"
;database_port = "3306"
;database_name = "vufind"

; Should SSL be enabled on connections? (Currently only supported for MySQL).
; Also requires appropriate extra_options settings below.
; IMPORTANT: when using Linux, if your database connection string above uses
; "localhost", MySQL will automatically use a Unix socket connection. To force
; an SSL connection, change "localhost" to the IP address (e.g. "127.0.0.1").
use_ssl = false

; When use_ssl above is true, the SSL certificate used by MySQL will not be
; verified by default. This is usually a self-signed certificate that cannot be
; easily verified. Skipping verification will not prevent traffic from being
; securely encrypted, it simply means that you trust the validity of the
; certificate being used by your server. If you do switch this to true in order
; to force certificate verification, additional configuration may be needed to
; successfully connect and ensure actual certificate verification occurs.
; For PHP, you should fill in the security-related settings in extra_options
; below; for Java (used by the MARC import tool), see the "Connecting Securely
; Using SSL" section of the Connector/J manual for details.
verify_server_certificate = false

; This setting can be used to send additional options to the Doctrine DB connection.
; This is useful for advanced features unsupported by settings above, such as
; detailed SSL security configuration.
;extra_options[client_key] = "/path/to/key"
;extra_options[client_cert] = "/path/to/cert"
;extra_options[ca_cert] = "/path/to/ca_cert"
;extra_options[ca_path] = "/path/to/ca"

; If your database (e.g. PostgreSQL) uses a schema, you can set it here:
;schema = schema_name

; The character set of the database -- utf8 and utf8mb4 are currently the only
; supported values, and utf8mb4 is the default if no value is set here. (Ignored
; for non-MySQL databases).
;charset = utf8
```

## `[Content]` & Externe Content-Provider
External Content is Optional. To use multiple, separate with a comma. Priority will be given by the order listed. Account id is separated with a colon, if no id is used then no colon is necessary.

IMPORTANT: Review content providers' terms of service before turning them on. Terms may change, and not all content sources are appropriate for all applications. The existence of functionality in VuFind does not imply suitability for any particular situation.

```ini
[Content]
; You can define the cover size used by each template: false (to disable covers)
; or size (small, medium, or large). A colon separated list may be used to try
; multiple sizes in a particular order. All legal template values and default
; values are reflected in the examples below. Uncomment the appropriate lines to
; make changes.
;coversize[checkedout] = small
;coversize[collection-info] = medium
;coversize[core] = medium
;coversize[holds] = small
;coversize[illrequests] = small
;coversize[list-entry] = medium
;coversize[RandomRecommend] = "small:medium"
;coversize[result-grid] = large
;coversize[result-list] = medium
;coversize[similar-items] = large
;coversize[storageretrievalrequests] = small

; Alternatively, if you wish to disable covers completely, you may set the
; coversize setting to false:
;coversize = false

; You can select Syndetics, LibraryThing, Summon, OpenLibrary,
; Contentcafe, Buchhandel, Google, BrowZine, ObalkyKnih, Orb, Koha, Demo,
; and/or LocalFile. Service-specific notes:
;   - BrowZine requires you to have BrowZine.ini configured appropriately.
;   - Buchhandel requires a valid token in the [Buchhandel] section.
;   - Google Books caching behavior can be customized in the [Cache_GoogleCover]
;       section.
;   - Koha requires the koha_cover_url setting below. Additionally, for
;       Koha to be a source of covers, its LocalCoverImages and
;       OPACLocalCoverImages system preferences must be turned on and local cover
;       images uploaded to the Koha system. Koha emits only two sizes of cover
;       images, so small and medium are treated the same in VuFind.
;   - Demo should only be used for testing. It will return an image with filename
;       "demo-cover-*" from the themes or no image. This is evenly distributed based on
;       the checksum of the record's parameters like id, isbn etc. Use Demo:true to activate
;       backlink functionality when ajaxcovers are active.
;   - LocalFile:PathToFile supports a combination of directory path information
;       and tokens for filename and image type. If you have multiple directories
;       in which you have stored coverimages, you can specify multiple paths to search
;       by specifying multiple LocalFile:PathToFile in the coverage images list below.
;       Allowed tokens:
;          %anyimage%         - Match known image file extensions (gif, jpg, etc.)
;          %isbn10%           - 10-digit ISBN
;          %isbn13%           - 13-digit ISBN
;          %issn%             - ISSN
;          %oclc%             - OCLC Number
;          %recordid%         - Bibliographic record ID
;          %size%             - Size (small/medium/large)
;          %source%           - Search backend of record (e.g. Summon, Solr, etc.)
;          %upc%              - UPC Number
;          %vufind-home%      - The VUFIND_HOME environment variable
;          %vufind-local-dir% - The VUFIND_LOCAL_DIR environment variable
;        Example: LocalFile:%vufind-local-dir%/path/to/file/%size%/issn/%issn%.%anyimage%
;   - ObalkyKnih could be configured in obalkyknih.ini file. You should also
;       add API URLs to img-src directive in contentsecuritypolicy.ini. As the
;       conditions of use of ObalkyKnih require backlinks to provider, you need to
;       also turn on ajaxcovers below - VuFind is able to show backlinks only
;       through AJAX at this time.
;   - Orb requires that you complete the [Orb] section. Cache settings can be
;       adjusted in the [Cache_OrbCover] section.
;   - Summon service takes a Serials Solutions client key, NOT Summon API key!
;coverimages     = Syndetics:MySyndeticsId,LibraryThing:MyLibraryThingId,Google,ObalkyKnih,OpenLibrary,Summon:MySerialsSolutionsClientKey,Buchhandel,Contentcafe:MyContentCafeID,BrowZine,LocalFile:PathToFile,Koha,Orb

; When using the Koha cover provider, you should fill in this setting:
;koha_cover_url = "https://localhost/cgi-bin/koha/opac-image.pl"

; This setting controls which services will have images cached on your local disk.
; Set to true to cache all applicable services. Set to false to disable caching. Set
; to a comma-separated list of services (e.g. "Syndetics,OpenLibrary") to cache only
; a subset of selected services. Default = true. Note that due to terms of service,
; some services will never have images cached even if caching is enabled.
coverimagesCache = false

; This setting controls if browsers will cache dynamically generated cover images.
; Note that this does not work if record drivers provide full thumbnail URLs directly.
; This is mainly intended for testing purposes.
;coverimagesBrowserCache = true

; This setting controls which proxied image URLs will be cached to local disk (when
; using the ?proxy= parameter of the standard /Cover/Show routes). The setting may
; contain one or more regular expressions matching hostnames. The example
; below will match any images from the mylibrary.edu domain; you can also use
; "/.*/" to turn on caching for all proxied images.
;coverproxyCache[] = "/.*\.?mylibrary\.edu/"
; BrowZine DOI icons:
coverproxyCache[] = "/assets\.thirdiron\.com/"

; This setting controls which hosts are allowed to provide cover images through
; the built-in cover proxy. Images from hostnames that do not match the regular
; expressions below will not be proxied. Be sure to define regular expressions
; using an end of string ($) marker to prevent abuse.
coverproxyAllowedHosts[] = "/assets\.thirdiron\.com$/"
coverproxyAllowedHosts[] = "/\.summon\.serialssolutions\.com$/"

; This setting controls the content types allowed through the cover proxy.
; Note that proxying SVG files is not recommended because of their potential for
; abuse in cross-site scripting attacks.
coverproxyAllowedTypes[] = "image/gif"
coverproxyAllowedTypes[] = "image/jpeg"
coverproxyAllowedTypes[] = "image/png"

; This setting controls how cover image URLs are loaded. They could be loaded as
; part of main request, or asynchronously. Asynchronous loading is disabled by
; default; to enable it, just uncomment the line below.
;ajaxcovers = true

; When ajaxcovers is set to true, this setting controls whether the AJAX Handler
; GetRecordCover renders a fallback template (record/coverReplacement.phtml) in case
; no cover image could be loaded.
useCoverFallbacksOnFail = false

; These settings control the image to display when no book cover is available.
; If makeDynamicCovers is not false and the GD library is installed, VuFind will draw
; cover images on the fly. See [DynamicCovers] below for more settings. If set to
; a non-Boolean value, for legacy reasons, the makeDynamicCovers setting will
; be used as the backgroundMode setting of [DynamicCovers] if that setting is unset.
;makeDynamicCovers = true

; Otherwise, you can use noCoverAvailableImage to specify a
; path relative to the base of your theme directory for a static image to display.
; If set to "false", no image will be displayed for unavailable covers.
noCoverAvailableImage = false

; You can select from Syndetics and/or the Guardian
;   Note: If the API key is omitted, e.g. "Guardian:", only the review title, byline,
;         Guardian logo and a link to the full Guardian page will be displayed
;   Note: The Guardian API changed in 2014; if you signed up before that date, you
;         may need to obtain a new API key for continued access.
;reviews         = Syndetics:MySyndeticsId,Guardian:MyGuardianKeyId

; You can only select Syndetics
;excerpts        = Syndetics:MySyndeticsId

; This setting can be used to hide review/excerpt tabs on the record page when
; no content is available from the providers. By default it is turned off. You
; can turn it on for all relevant tabs by setting it to true, or you can turn
; it on for a comma-separated list of values (e.g. "reviews" or "excerpts" or
; "reviews,excerpts") for selective activation. Note that hiding empty tabs will
; make your record pages slower, since it will require extra communication with
; content providers.
;hide_if_empty = reviews,excerpts

; You can only select Syndetics to add summary information to
; the description tab.
;summaries = Syndetics:MySyndeticsId

; You can select from Syndetics or ObalkyKnih to load Tables of Contents
;toc = Syndetics:MySyndeticsId,ObalkyKnih

; You can only select Syndetics
;authorNotes = Syndetics:MySyndeticsId

; You can select from Wikipedia
; See also the AuthorInfo recommendation module setting in searches.ini; this
; includes notes on improving the accuracy of Wikipedia retrievals.
; Note: The Wikipedia Commons URL should also be enabled in the related
; img-src setting of contentsecuritypolicy.ini to support author images.
; Note for Windows users: If using Wikipedia, you may need to increase your Apache
; heap size settings.  For details, see: https://vufind.org/jira/browse/VUFIND-630
authors         = Wikipedia

; You can select from Google, OpenLibrary, HathiTrust.  You should consult
; https://developers.google.com/books/branding before using Google Book Search.
; If using a CSP, you may also need to add settings to contentsecuritypolicy.ini.
;previews       = Google,OpenLibrary,HathiTrust

; This setting controls whether or not cover images are linked to previews when
; available. Legal settings are false (never link), * (always link; default), or
; a comma-separated list of templates in which linking should occur (see coversize
; above for a list of legal values).
;linkPreviewsToCovers = *

; Possible HathiRights options = pd,ic,op,orph,und,umall,ic-world,nobody,pdus,cc-by,cc-by-nd,
; cc-by-nc-nd,cc-by-nc,cc-by-nc-sa,cc-by-sa,orphcand,cc-zero,und-world,icus
; Default is "pd,ic-world" if unset here.
; See www.hathitrust.org/rights_database#Attributes for full details
;HathiRights    = pd,ic-world,cc-by,cc-by-nd,cc-by-nc-nd,cc-by-nc,cc-by-nc-sa,cc-by-sa,cc-zero,und-world

; Possible GoogleBooks options full,partial,noview
; options can be set for each / either of link or tab
; Link makes a button appear in search results / record view
; Tab makes a tab with an embedded preview appear on record view
; Default is "GoogleOptions['link'] = full,partial" if nothing
; is set here.
; see https://developers.google.com/books/docs/dynamic-links#json-results-format
;GoogleOptions['link']  = full,partial
;GoogleOptions['tab']  = partial

; OpenLibrary currently offers the same options/default as GoogleBooks (above):
;OpenLibraryOptions  = full,partial

; An API key is needed to interact with the Europeana API (see the EuropeanaResults
; recommendation module in searches.ini for more information)
;europeanaAPI = INSERTKEY

; Geographic Display
; These configuration settings have been superseded by the geofeatures.ini file.
; See the [MapTab] section of the geofeatures.ini file for more information.
```

### `[DynamicCovers]`, `[Buchhandel]`, `[QRCode]`, `[Syndetics]`, `[Orb]`, `[Contentcafe]`, `[Summon]`, `[Relais]`, `[DPLA]`

```ini
; This section controls the behavior of the cover generator when makeDynamicCovers
; above is non-false.
;
; Note that any of these settings may be filtered to be size-specific by subscripting
; the key with a size. You can use a key of * for a default to use when a specific
; size is not matched. This allows adjustment of certain elements for different
; thumbnail sizes. See the "size" setting below for an example.
[DynamicCovers]
; This controls the background layer of the generated image; options:
; - solid: display a solid color
; - grid: display a symmetrical random pattern seeded by title/callnumber
;backgroundMode = grid
; This controls the text layer of the generated image; options:
; - default: display a title at the top and an author at the bottom
; - initial: display only the first letter of the title as a stylized initial
;textMode = default
; Font files specified here should exist in the css/font subdirectory of a theme.
; Some options are available by default inside the root theme.
;authorFont = "Roboto-Light.ttf"
;titleFont = "RobotoCondensed-Bold.ttf"
; In 'default' textMode, covers are generated using title and author name; VuFind
; will try to display everything by doing the following: break the title into
; lines, and if the title is too long (more than maxTitleLines lines), it will
; display ellipses at the last line.
; All text will be drawn using the specified textAlign alignment value using the
; relevant titleFontSize or authorFontSize setting, except that author names will
; be reduced to the minAuthorFontSize option if needed, and if that doesn't make
; it fit, text will be aligned left and truncated.
; When using 'initial' textMode, maxTitleLines and author-related settings are
; ignored as they do not apply.
;textAlign = center
;titleFontSize = 9
;authorFontSize = 8
;minAuthorFontSize = 7
;maxTitleLines = 4
; All color options support the same basic set of values:
; - The 16 named colors from HTML4
; - Arbitrary HTML hex colors in the form #RRGGBB (e.g. #FFFF00 for yellow)
; Some color options also support additional options.
; - authorFillColor,titleFillColor: the main color used
; - authorBorderColor,titleBorderColor: the color used to make a border; "none" is
;   a legal option in addition to colors.
; - baseColor: When using grid backgrounds, you may also choose a base color drawn
;   beneath the grid. Default is white.
; - accentColor: When using solid backgrounds, this is the background color; when
;   using grid backgrounds, this is the color of the grid pattern beneath the text.
;   You may set this to "random" to select a random color seeded with text from
;   the cover and adjusted with the "lightness" and "saturation" settings below.
;titleFillColor = black
;titleBorderColor = none
;authorFillColor = white
;authorBorderColor = black
;baseColor = white
;accentColor = random
; Note: lightness and saturation are only used when accentColor = random. Legal
; ranges are 0-255 for each value.
;lightness = 220
;saturation = 80
; These settings control the size of the image -- if size is a single number, a
; square will be created; if it is a string containing an "x" (i.e. 160x190) it
; defines a WxH rectangle. wrapWidth constrains the text size (and must be no
; larger than the width of the canvas). topPadding and bottomPadding push the
; text away from the edges of the canvas.
;size[*] = 128
;size[medium] = 200
;size[large] = 500
;topPadding = 19
;bottomPadding = 3
;wrapWidth = 110

; This section is needed for Buchhandel.de cover loading. You need an authentication
; token. It may also be necessary to customize your templates in order to comply with
; terms of service; please look at http://info.buchhandel.de/handbuch_links for
; details before turning this on.
[Buchhandel]
url = "https://api.vlb.de/api/v1/cover/"
; token = "XXXXXX-XXXX-XXXXX-XXXXXXXXXXXX"

[QRCode]
; This setting controls the image to display when no qrcode is available.
; The path is relative to the base of your theme directory. Default is
; "images/noQRCode.gif".
;noQRCodeAvailableImage = images/noQRCode.gif
; Should we show QR codes in search results?
;showInResults = true
; Should we show QR codes on record pages?
;showInCore = true

[Syndetics]
; When the Syndetics cover image fallback is enabled, it will return generic covers based
; on information Syndetics has related to the ISBN number or other number given. This
; will prevent other fallbacks in VuFind from being tried, such as other cover image sources.
; If the Syndetics cover image fallback is disabled, VuFind will query for the metadata first,
; and only return an image if the Syndetics metadata returns a real cover image.
use_syndetics_cover_image_fallback = false
; timeout value (in seconds) for API calls:
timeout = 10

; Orb does stand for Outil de Recherche Biblbiographique (tool for bibliographic
; search). It's a french database of bibliographic information available by subscription.
; Web site: https://www.base-orb.fr
; API: http://doc.api.base-orb.fr/
; It could be used in VuFind as cover image provider.
[Orb]
url = "api.base-orb.fr/v1"
;user = api_test_user
;key = api_access_key

; Content Cafe is a subscription service from Baker & Taylor. If you are using this
; service (see the [Content] section above for details), you MUST uncomment and set
; the password (pw) setting. You may also change the API base URL (url) if needed.
[Contentcafe]
;url              = "http://contentcafe2.btol.com"
;pw               = "xxxxxx"

; Summon is optional; this section is used for your API credentials. apiId is the
; short, human-readable identifier for your Summon account; apiKey is the longer,
; non-human-readable secret key. See also the separate Summon.ini file.
;[Summon]
;apiId        = myAccessId
;apiKey       = mySecretKey

; This section must be filled in to use Relais functionality. When
; activated, this function will allow users to place ILL requests on unavailable
; items through the record holdings tab.
;[Relais]
;symbol="XYZ"
;pickupLocation = "DEFAULT"
;patronForLookup="99999999"
;apikey="your-relais-api-key-goes-here"
;timeout = 500
;loginUrl = https://mysite.relais-host.com/user/login.html
;group="DEMO"
;authenticateurl="https://demo.relais-host.com/portal-service/user/authentication"
;availableurl="https://demo.relais-host.com/dws/item/available"
;addurl="https://demo.relais-host.com/dws/item/add"

; DPLA key -- uncomment and fill in to use DPLATerms recommendations (see also
; searches.ini).
;[DPLA]
;apiKey = http://dp.la/info/developers/codex/policies/#get-a-key
```

## `[IdentifierLinks]`
These settings affect dynamic identifier-based link inclusion; this can provide links to full text or contextual information based on identifiers like DOI, ISBN or ISSN.

```ini
; This setting controls whether or not ID-based links are enabled, and which
; API is used to fetch the data. Currently supported options: BrowZine (requires
; credentials to be configured in BrowZine.ini), Demo (which generates fake data
; to simulate use of a real service, for testing), Unpaywall or false (to disable).
; Disabled by default. You may also use a comma-separated list of resolvers if you
; want to try multiple sources.
;resolver = BrowZine

; If you use multiple values in the resolver setting above, you can determine how the
; software should behave when multiple resolvers return results for the same ID.
; You can choose "first" (only return results from the first matching resolver --
; the default behavior) or "merge" (merge together all results and show them all).
;multi_resolver_mode = first

;unpaywall_api_url = "https://api.unpaywall.org/v2"
; Unpaywall needs an email address, see https://unpaywall.org/products/api
;unpaywall_email = "your@email.org"

; The following settings control where ID-based links are displayed:
show_in_results = true      ; include in search results
show_in_record = false      ; include in core record metadata
show_in_holdings = false    ; include in holdings tab of record view

; This setting controls which types of identifiers are used to generate links; if
; the setting is omitted, all supported types (DOI, ISBN and ISSN) will be used:
supportedIdentifiers[] = doi
supportedIdentifiers[] = isbn
supportedIdentifiers[] = issn

; Whether to load any third-party icons for the ID services via VuFind's cover
; loader proxy to avoid any privacy implications. Ensure that the necessary domains
; are allowed in Content/coverproxyCache[] setting. Default is false.
proxy_icons = true

; Whether to open links in a new window. Default is false.
;new_window = false
```

## `[OpenURL]`, `[LinkIQ]` & `[EZproxy]`
These settings affect OpenURL generation and presentation; OpenURLs are used to help users find resources through your link resolver and to manage citations in Zotero.

```ini
[OpenURL]
; If a resolver base URL is enabled, it will be used to link from records to your
; OpenURL resolver. An OpenURL resolver is typically used to e.g. link to full text
; from article metadata, but it may provide other services too. Extra parameters may
; be added if necessary.
; For LinkIQ (the API used with EBSCO Full Text Finder), the url format is the
; following. {PROFILE} needs to be replaced with the actual profile code (like
; ns123456.main.ftf), and also a password is required in the [LinkIQ] section).
; Remember also to set resolver to "linkiq" below.
; https://api.ebsco.io/ftf/ftfaccount/{PROFILE}/openurl
;url             = "http://openurl.myuniversity.edu/sfx_local"

; This string will be included as part of your OpenURL referer ID (the full string
; will be "info:sid/[your rfr_id setting]:generator").  You may be able to configure
; special behavior in your link resolver based on this ID -- for example, you may
; wish to prevent the resolver from linking to VuFind when links came from VuFind
; (to avoid putting a user in an infinite loop).
rfr_id          = vufind.svn.sourceforge.net

; By specifying your link resolver type, you can allow VuFind to optimize its
; OpenURLs for a particular platform.  Current legal values: "sfx", "360link",
; "JOP", "Redi", "Alma", "demo", "linkiq" or "generic" (default is "generic"
; if commented out; "demo" generates fake values for use in testing the embed
; setting below).
; Note that linkiq (used with EBSCO Full Text Finder) requires additional settings
; for the connection in the [LinkIQ] section.
;resolver        = sfx

; Some link resolver drivers can filter resource links based on specific criteria.
; This setting indicates which filter reasons should NOT be used to exclude
; resource links. This is currently ONLY supported by the Alma resolver driver.
; (default is "Date Filter" if commented out, for backwards compatibility)
; ignoredFilterReasons[] = "Date Filter"

; If you want OpenURL links to open in a new window, set this setting to the
; desired Javascript window.open parameters.  If you do not want a new window
; to open, set this to false or comment it out.
window_settings = "toolbar=no,location=no,directories=no,buttons=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=550,height=600"

; If you want to display a graphical link to your link resolver, uncomment the
; settings below.  graphic should be a URL; graphic_width and graphic_height
; should be sizes in pixels.
;graphic = "http://myuniversity.edu/images/findIt.gif"
;graphic_width = 50
;graphic_height = 20

; If your link resolver can render an image in response to an OpenURL, you can
; specify the base URL for image generation here:
;dynamic_graphic = "http://my-link-resolver/image"

; If dynamic_graphic is set above, the dynamic image can be used instead of the
; standard text or static-image-based OpenURL link (true), it can be disabled
; (false), or it can be displayed in addition to the regular link ("both").
;image_based_linking_mode = both

; The following settings control where OpenURL links are displayed:
show_in_results = true      ; include in search results
show_in_record = false      ; include in core record metadata
show_in_holdings = false    ; include in holdings tab of record view

; If set to true, this setting will attempt to embed results from the link
; resolver directly in search results instead of opening a new window or page.
; This will override the window_settings option if set! Embedding is currently
; unsupported when the resolver setting above is set to "generic".
embed = false

; When embed is true and this is set to true results from the link resolver will
; be loaded automatically (default is false, which requires a user click to trigger
; the loading).
embed_auto_load = false

; When embed is true, you can set this to an absolute path on your system in order
; to cache link resolver results to disk.
;resolver_cache = /usr/local/vufind/resolver_cache

; This setting controls whether we should display an OpenURL link INSTEAD OF other
; URLs associated with a record (true) or IN ADDITION TO other URLs (false).
replace_other_urls = true

; Additional resolver settings required for EBSCO LinkIQ (available with EBSCO
; Full Text Finder).
[LinkIQ]
; Profile Password for the service:
;password = ""
; Base URL for the "More Options" link to Full Text Finder. Leave empty to disable.
;moreOptionsUrl = "https://resolver.ebsco.com/c/{PROFILE_OPID}/result"

; EZproxy is optional.  This section only needs to exist if you
; are using EZProxy to provide off-site access to online materials.
;[EZproxy]
;host            = http://proxy.myuniversity.edu
;prefixLinks = true
;prefixLinksWebServiceUrl = http://localhost:8888/proxyUrl
;prefixLinksWebServiceCacheLifetime = 600
;secret = "verysecretpassword"
;secret_hash_method = "SHA512"
;anonymous_ticket = true
;disable_ticket_auth_logging = true
```

## `[RefWorks]`, `[EndNoteWeb]`, `[Zotero]` & `[OAI]`

```ini
; These settings affect RefWorks record exports.  They rarely need to be changed.
[RefWorks]
vendor          = VuFind
url             = https://www.refworks.com

; These settings affect EndNote Web record exports.  They rarely need to be changed.
[EndNoteWeb]
vendor          = VuFind
url             = https://www.myendnoteweb.com/EndNoteWeb.html

; These settings affect Zotero record exports. You will need to register your
; VuFind instance with Zotero at https://www.zotero.org/oauth/apps and enter the
; credentials below for the user-facing OAuth authentication to work properly.
[Zotero]
; Client key
identifier = ""
; Client secret
secret = ""
; Callback URI pointing to VuFind's /Zotero/Callback action. Normally left
; blank (VuFind will automatically create it based on the used server address).
;callback_uri = ""

; These settings affect your OAI server if you choose to use it.
;[OAI]
;identifier       = myuniversity.edu
;repository_name  = "MyUniversity Catalog"
;admin_email      = oai@myuniversity.edu
;page_size        = 1000
;set_field        = "format"
;set_query['eod_books'] = "institution:kfu AND publishDateRange:[1911 TO 1911]"
;set_query['eod_ebooks'] = "format:eBook"
;default_query = "institution:kfu"
;vufind_api_format_fields = "id,authors,cleanIsbn,cleanIssn,formats,title"
;record_format_filters[marc21] = "record_format:marc"
;delete_lifetime = 365
;use_cursor = true
```

## `[Proxy]` & `[Http]`

```ini
; Proxy Server is Optional.
[Proxy]
;host = your.proxy.server
;port = 8000
;type = socks5
;type = socks5_hostname
;local_addresses = "@^(localhost|127(\.\d+){3}|\[::1\])@"
;allow_forwarded_ips = "HTTP_X_REAL_IP:single,HTTP_X_FORWARDED_FOR:last"
;forwarded_ip_filter[] = 1.2.3.4

; Default HTTP settings can be loaded here. These values will be passed to
; the \Laminas\Http\Client's setOptions method.
[Http]
;sslcapath = "/etc/ssl/certs" ; e.g. for Debian systems
;sslcafile = "/etc/pki/tls/cert.pem" ; e.g. for CentOS systems
;timeout = 30 ; default timeout if not overridden by more specific code/settings
;adapter = 'Laminas\Http\Client\Adapter\Curl'
;curloptions[52] = true
```

## `[Spelling]`
Note: These settings affect the VuFind side of spelling suggestions; you may also wish to adjust some Solr settings in `solr/biblio/conf/schema.xml` and `solr/biblio/conf/solrconfig.xml`.

```ini
enabled = true
; Number of suggestions to display on screen. This list is filtered from
;   the number set in solr/biblio/conf/solrconfig.xml so they can differ.
limit   = 3
; Show the full modified search phrase on screen
;   rather then just the suggested word
phrase = false
; Offer expansions on terms as well as basic replacements
expand  = true
; Set the list of spellcheck dictionaries used in a Solr query. The
; available dictionaries are defined in solrconfig.xml.
dictionaries[] = default
dictionaries[] = basicSpell
;dictionaries[] = direct
; This setting skips spell checking for purely numeric searches; spelling
; suggestions on searches for ISBNs and OCLC numbers are not generally very
; useful.
skip_numeric = true
```

## `[Logging]`
These settings control what events are logged and where the information is stored. VuFind supports four logging levels: `alert` (severe fatal error), `error` (fatal error), `notice` (non-fatal warning) and `debug` (informational), as well as PSR-3 levels in UPPERCASE. Each logging level can be broken down into five levels of verbosity (e.g. `alert-2` or `error-5`).

```ini
;database       = log_table:alert,error,notice,debug
; ************ setin config.ini ********************
file           = php://stderr:alert,error,notice
; Example: everything except the debug level:
;file           = /var/log/vufind.log:alert,error,notice,INFO
; email          = alerts@myuniversity.edu:alert-5,error-5
;slack = #channel_name:alert,error
;slackurl = https://hooks.slack.com/services/your-private-details
;slackname = "VuFind Log"
;office365_url = "https://outlook.office.com/webhook/xxx/IncomingWebhook/yyy"
;office365 = alert,error
;office365_title = "VuFind Log"
;reference_id = false

; A comma-separated list of event types to enable logging of audit events to the
; database audit_event table. Default is to only log payment events.
; Built-in event types: ils, payment, user
; Run "php $VUFIND_HOME/public/index.php util expire_audit_events" periodically.
log_audit_events = "payment"
```

## `[Parent_Config]`
This section can be used to specify a "parent configuration" from which the current configuration file will inherit.

```ini
;path = /usr/local/vufind/application/config/config.ini
;relative_path = ../parentconfig/config.ini
;use_parent_dir = true
;override_full_sections = "Languages,AlphaBrowse_Types"
;merge_array_settings = false
```

## `[Languages]` & `[LanguageSettings]`
This section controls which language options are available to your users. The order of the settings is significant. Be sure that this section includes the default language set in the `[Site]` section.

```ini
[Languages]
;debug       = "Debug"
de          = "German"
en          = "English"              ; American spellings
;en-gb       = "English"              ; British spellings
;es          = "Spanish"
;fr          = "French"
;it          = "Italian"
;ja          = "Japanese"
;nl          = "Dutch"
;nl-be       = "Flemish Dutch"
;pt          = "Portuguese"
;pt-br       = "Brazilian Portugese"
;pt-br       = "Brazilian Portuguese"
;zh-cn       = "Simplified Chinese"
;zh          = "Chinese"
;tr          = "Turkish"
;he          = "Hebrew"
;ga          = "Irish"
;cy          = "Welsh"
;el          = "Greek"
;ca          = "Catalan"
;eu          = "Basque"
;ru          = "Russian"
;cs          = "Czech"
;fi          = "Finnish"
;sv          = "Swedish"
;pl          = "Polish"
;da          = "Danish"
;sl          = "Slovene"
;ar          = "Arabic"
;bn          = "Bengali"
;gl          = "Galician"
;vi          = "Vietnamese"
;hr          = "Croatian"
;hi          = "Hindi"
;hy          = "Armenian"
;uk          = "Ukrainian"
;se          = "Northern Sámi"
;mn          = "Mongolian"
;mi          = "Maaori"
;ta          = "Tamil"

[LanguageSettings]
; Comma-separated list of languages to display in right-to-left mode
rtl_langs = "ar,he"
```

## `[Browse]`
This section controls the behavior of the Browse module.

```ini
result_limit    = 100
tag             = true      ; allow browsing of Tags
dewey           = false     ; allow browsing of Dewey Decimal call numbers
lcc             = true      ; allow browsing of LC call numbers
author          = true      ; allow browsing of authors
topic           = true      ; allow browsing of subject headings
genre           = true      ; allow browsing of genre subdivisions
region          = true      ; allow browsing of region subdivisions
era             = true      ; allow browsing of era subdivisions
;alphabet_letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"
;alphabetical_order = true
```

## `[BulkActions]`, `[Export]` & `[BulkExport]`

```ini
[BulkActions]
; Set the limit of items for each action. Set 0 to disable the action and hide the button.
limits["default"] = 100
limits["email"] = 100
limits["export"] = 100
limits["print"] = 100
limits["saveCart"] = 100
limits["delete"] = 100

; Valid options: bulk, record, true (only record), false (disabled)
[Export]
RefWorks = false
EndNote = false
EndNoteWeb = false
; Note: ZoteroWebLibrary requires proper settings in [Zotero] section!
;ZoteroWebLibrary = "record,bulk"
MARC = false
MARCXML = false
RDF = false
BibTeX = false
RIS = false
ExtEdm           = "record,bulk"

[BulkExport]
;defaultType = download
```

## `[Item_Status]`
This section controls how item status information is presented in search results.

```ini
; Options: first, all, msg, group (valid for multiple_locations only)
multiple_call_nos = first
multiple_locations = msg
preferred_service = "loan"
show_full_status = false
callnumber_handler = false
load_batch_wise = true
load_observable_only = true
include_holdings_text_fields = true
;displayed_holdings_text_fields[] = 'holdings_notes'
;displayed_holdings_text_fields[] = 'summary'
```

## `[Record]`
This section controls the behavior of the Record module.

```ini
next_prev_navigation = false
first_last_navigation = false
permanent_link = true
jump_to_single_search_result = false
;marc_links = "760,762,765,767,770,772,773,774,775,776,777,780,785,787"
;marc_links_link_types = id,oclc,dlc,isbn,issn,title
;marc_links_use_visibility_indicator = false
;marcPublicationInfoSeparator = " "
preferredMarcFields = "fullrecord"
;replaceMarc260 = false
;remote_marc_url = http://127.0.0.1/%s
hide_holdings[] = "World Wide Web"
; Available options: Channels, Bookplate, MoreByAuthorSolr, Similar, WorldCat2Similar
related[] = "Similar"
;related[] = "MoreByAuthorSolr"
;related[] = "Bookplate:Filename:Section"
;similar_carousel_items = 40
;bookplate_full = https://your-institution.edu/bookplates/%%img%%-full.jpg
;bookplate_thumb = https://your-institution.edu/bookplates/%%thumb%%-thumb.jpg
;bookplate_titles_field = "donor_str_mv"
;bookplate_images_field = "donor_code_str_mv"
;bookplate_thumbnails_field = "donor_code_str_mv"
;bookplate_display_title = true
;citation_formats = APA,Chicago,MLA
citation_formats = ""
;subjectLimit = false
;holdingsItemLimit = false
;alwaysDisplayIndexRecordInStaffView = false
includeSchemaOrgMetadata = true
;marcSubjectHeadingsSort = "numerical"
;getThisEnabled = true
```

## `[AlphaBrowse]`, `[AlphaBrowse_Types]` & `[AlphaBrowse_Extras]`

```ini
[AlphaBrowse]
page_size = 20
rows_before = 0
;topic_browse_separator = " > "
highlighting = false
bypass_default_filters = true

[AlphaBrowse_Types]
topic = "By Topic"
author = "By Author"
title = "By Title"
lcc = "By Call Number"
;dewey = "By Call Number"

[AlphaBrowse_Extras]
title = "author:format:publishDateRange"
lcc = title
dewey = title
```

## `[Security]`
This section allows you to configure the values used for Cryptography.

```ini
; HMACkey = set in config.ini

; When encrypting/decrypting data, should we use the legacy Laminas version of the
; pbkdf2 algorithm (true, for backward compatibility with data from previous releases)
; or should we use the native PHP version of the algorithm (false).
; If you change this value, you should re-encrypt all of your data by changing your
; encryption key via the util/switch_db_hash command line utility.
legacyPbkdf2 = true
```

## `[Cache]`, `[ConfigCache]` & `[CachingDownloader]`

```ini
[Cache]
;disabled = true
;ttl = 0
;umask = 022
;dir_permission = 0700
;file_permission = 0600

[ConfigCache]
cacheName = "config"
reloadOnFileChange = true
disabled = false

[CacheConfigHandler_ini]
disabled = true

[CacheConfigName_searchspecs]
disabled = false

[Cache_GoogleCover]
[Cache_OrbCover]

[CachingDownloader]
;disabled = true
```

## `[Collections]` & `[Hierarchy]`

```ini
[Collections]
;collections = true
;defaultTab = CollectionList
;browseType = Index
;browseDelimiter = "{{{_ID_}}}"
;browseLimit = 20
route[record] = collection
route[search2record] = search2collection

[Hierarchy]
driver = Default
showTree = true
search = true
treeSearchLimit = 100
simpleContainerLinks = true
validateHierarchySequences = true
;hide_preview_in_narrow_displays = true
```

## `[Feedback]`
Forms are configured in `FeedbackForms.yaml`.

```ini
; *********** set in config.ini! **********************
;tab_enabled       = true
;recipient_email   = "feedback@myuniversity.edu"
;recipient_name    = "Your Library"
;email_subject     = "VuFind Feedback"
;sender_email      = "noreply@vufind.org"
;sender_name       = "VuFind Feedback"
;ignored_senders[] = "@baddomain.com"
;blocked_senders[] = "/^(itchy|scratchy)@dontwantfeedbackfrom\.com/"
```

## `[GoogleTagManager]`, `[GoogleAnalytics]`, `[Matomo]` & `[Piwik]`

```ini
;[GoogleTagManager]
;gtmContainerId = "GTM-1234567"

;[GoogleAnalytics]
;apiKey = "mykey"
;create_options_js = "{cookie_flags: 'max-age=7200;secure;samesite=none'}"

[Matomo]
url = "set in config.ini"
site_id = "set in config.ini"
;custom_dimensions[Facets] = 1
;custom_dimensions[FacetTypes] = 2
;custom_dimensions[SearchType] = 3
;custom_dimensions[SearchBackend] = 4
;custom_dimensions[Sort] = 5
;custom_dimensions[Page] = 6
;custom_dimensions[Limit] = 7
;custom_dimensions[View] = 8
;custom_dimensions[RecordFormat] = 9
;custom_dimensions[RecordData] = 10
;custom_dimensions[RecordInstitution] = 11
;custom_dimensions[Context] = 12
;custom_variables = true
;searchPrefix = "SiteA|"
;disableCookies = true

; Deprecated in favor of [Matomo]
[Piwik]
;url = "http://server.address/piwik/"
;site_id = 1
```

## `[SearchTabs]`, `[SearchHistoryLabels]`, `[Captcha]` & `[SearchPlaceholder]`

```ini
[SearchTabs]
;Solr = Catalog
;Summon = Summon
;WorldCat2 = WorldCat
;Solr:filtered = "Catalog (Main Building Books)"
;EDS = "EBSCO Discovery Service"
;EIT = "EBSCO Integration Toolkit"
;Primo = "Primo Central"

[SearchTabsFilters]
;Solr:filtered[] = 'building:"main library"'
;Solr:filtered[] = "format:book"

[SearchTabsPermissions]
;EIT = access.EITModule
;Primo = access.PrimoModule

[SearchTabsSettings]
;show_result_counts = false

[SearchHistoryLabels]
;Solr = Catalog
;Summon = Summon
;WorldCat2 = WorldCat
;SolrWeb = "Library Website"
;EDS = "EBSCO Discovery Service"

;[Captcha]
;types[] = recaptcha
;forms = *
;image_length = 8
;image_width = 200
;image_height = 50
;image_fontSize = 24
;image_dotNoiseLevel = 100
;image_lineNoiseLevel = 5
;action_interval = 60
;time_from_session_start = 0
;recaptcha_siteKey  = "get your reCaptcha key at"
;recaptcha_secretKey = "https://www.google.com/recaptcha/admin/create"
;recaptcha_secretKey_file = /path/to/secret
;recaptcha_theme      = light

[SearchPlaceholder]
default          = "search find discover"
SolrPerson       = "search for persons"
SolrCorporation  = "search for corporations"
SolrEvent        = "search for events"
SolrWork         = "search for works"
SolrReserves = reserves_search_placeholder
```

## `[Social]` & `[List_Sorting]`

```ini
[Social]
comments = disabled
rating = false
remove_rating = true
user_content_page_size = 50
lists = disabled
lists_default_limit   = 20
;lists_limit_options   = 10,20,40,60,80,100
multi_page_favorites_selection = true
checkbox_select_all_favorites_type = both
lists_view=full
tags = disabled
listTags = disabled
max_tag_length = 64
case_sensitive_tags = false
show_solr_options_in_tag_search = false

[List_Sorting]
title = sort_title
author = sort_author
year DESC = sort_year
year = sort_year_asc
last_saved DESC = sort_saved
last_saved = sort_saved_asc
```

## `[API]`, `[API_Keys]`, `[Sorting]`, `[Allowed_HTML_Contexts]` & `[HTML_Rendering_Contexts]`

```ini
[API]
description = "The REST API provides access to search functions and records contained in the search index."
;termsOfServiceUrl = "https://something"

[API_Keys]
;mode = 'optional'
;token_salt =
;header_field = 'X-API-KEY'
;key_limit = 5

[Sorting]
;use_locale_sorting = true

[Allowed_HTML_Contexts]
;title = false
;title-alt = false
;summary = false

[HTML_Rendering_Contexts]
;allowed_elements[default] = ""
;allowed_elements[heading] = "a,b,br,em,i,span,strong,sub,sup,u"
;allowed_elements[link] = "abbr,acronym,b,bdo,big,br,cite,dfn,em,i,img,q,samp,small,span,strong,sub,sup,var"
```

## `[DataProvider]`
Struktur für Datenlieferanten:
- `<code>.label`: Anzeigename
- `<code>.homepage`: URL der Institution/Quelle
- `<code>.match`: Interner Match-String (z. B. GND-ID)
- `<code>.facet`: Facettenwert (z. B. `0/Name/` oder direkter Wert wie bei `dracor.facet = "DraCor"` statt `;dracor.facet = "0/DraCor/"`)
