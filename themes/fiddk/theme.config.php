<?php
return [
    'extends' => 'news',
    'favicon' => 'fid-dk_favicon.png',
    'helpers' => [
      'factories' => [
        'Fiddk\View\Helper\Fiddk\RecordDataFormatter' => 'Fiddk\View\Helper\Fiddk\RecordDataFormatterFactory',
        //'Fiddk\View\Helper\Fiddk\DateTime' => 'VuFind\View\Helper\Root\DateTimeFactory',
      ],
      'aliases' => [
        // Overrides
        //'VuFind\View\Helper\Root\Record' => 'Fiddk\View\Helper\Fiddk\Record',
        'VuFind\View\Helper\Root\RecordDataFormatter' => 'Fiddk\View\Helper\Fiddk\RecordDataFormatter',
        //'VuFind\View\Helper\Root\DateTime' => 'Fiddk\View\Helper\Fiddk\DateTime',
      ],
    ],
];
