<?php
return [
    'extends' => 'bootstrap3',
    'js' => [
        'klaro-config.js',
        'klaro-no-css.js',
    ],
    'favicon' => 'fid-dk_favicon.png',
    'helpers' => [
      'factories' => [
        'Fiddk\View\Helper\Fiddk\RecordDataFormatter' => 'Fiddk\View\Helper\Fiddk\RecordDataFormatterFactory',
        'Fiddk\View\Helper\Fiddk\Piwik' => 'VuFind\View\Helper\Root\PiwikFactory',
        'Fiddk\View\Helper\Fiddk\LayoutClass' => 'VuFind\View\Helper\Bootstrap3\LayoutClassFactory',
        //'Fiddk\View\Helper\Fiddk\DateTime' => 'VuFind\View\Helper\Root\DateTimeFactory',
      ],
      'aliases' => [
        // Overrides
        'VuFind\View\Helper\Root\Piwik' => 'Fiddk\View\Helper\Fiddk\Piwik',
        'VuFind\View\Helper\Root\RecordDataFormatter' => 'Fiddk\View\Helper\Fiddk\RecordDataFormatter',
        'VuFind\View\Helper\Bootstrap3\LayoutClass' => 'Fiddk\View\Helper\Fiddk\LayoutClass',
        //'VuFind\View\Helper\Root\DateTime' => 'Fiddk\View\Helper\Fiddk\DateTime',
      ],
    ],
];
