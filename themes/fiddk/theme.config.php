<?php
return array(
    'extends' => 'newsstream',
    'css' => array(
      //'fiddk.css:screen, projection',
    ),
    'less' => array(
        'active' => false,
        'compiled.less'
    ),
    'favicon' => 'fid-dk_favicon.png',
    'helpers' => array(
      'factories' => array(
        'record' => 'fiddk\View\Helper\fiddk\Factory::getRecord',
        'recorddataformatter' => 'fiddk\View\Helper\fiddk\RecordDataFormatterFactory',
      ),
    ),
);
