<?php

return [
    'extends' => 'bootstrap5',

    'js' => [
        'klaro-config.js',
        'klaro-no-css.js',
    ],
    'favicon' => 'fid-dk_favicon.png',
    'helpers' => [
        'factories' => [
            \Fiddk\View\Helper\Fiddk\LayoutClass::class => \VuFind\View\Helper\Bootstrap5\LayoutClassFactory::class,
            \Fiddk\View\Helper\Fiddk\Piwik::class => \VuFind\View\Helper\Root\PiwikFactory::class,
            \Fiddk\View\Helper\Fiddk\Record::class => \VuFind\View\Helper\Root\RecordFactory::class,
            \Fiddk\View\Helper\Fiddk\RecordDataFormatter::class =>
                \Fiddk\View\Helper\Fiddk\RecordDataFormatterFactory::class,
        ],
        'aliases' => [
            'layoutClass' => \Fiddk\View\Helper\Fiddk\LayoutClass::class,
            'piwik' => \Fiddk\View\Helper\Fiddk\Piwik::class,
            'record' => \Fiddk\View\Helper\Fiddk\Record::class,
            'recordDataFormatter' => \Fiddk\View\Helper\Fiddk\RecordDataFormatter::class,
        ],
    ],
];
