<?php
return [
    'extends' => 'news',
    'less' => [
        'active' => false,
        'compiled.less'
    ],
    'favicon' => 'fid-dk_favicon.png',
    'helpers' => [
      'factories' => [
        'Fiddk\View\Helper\Fiddk\RecordDataFormatter' => 'Fiddk\View\Helper\Fiddk\RecordDataFormatterFactory',
      ],
      'aliases' => [
        // Overrides
        //'VuFind\View\Helper\Root\Record' => 'Fiddk\View\Helper\Fiddk\Record',
        'VuFind\View\Helper\Root\RecordDataFormatter' => 'Fiddk\View\Helper\Fiddk\RecordDataFormatter',
      ],
    ],
];
