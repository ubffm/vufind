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
        ],
        'aliases' => [
            'layoutClass' => \Fiddk\View\Helper\Fiddk\LayoutClass::class,
        ],
    ],
];
