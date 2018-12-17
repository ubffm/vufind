<?php
namespace Fiddk\Module\Configuration;

$config = [
  'controllers' => [
    'factories' => [
      'Fiddk\Controller\ContentController' => 'VuFind\Controller\AbstractBaseFactory',
      'Fiddk\Controller\DproviderController' => 'VuFind\Controller\AbstractBaseFactory',
      //'ajax' => 'fiddk\Controller\Factory::getAjaxController',
      //'record' => 'fiddk\Controller\Factory::getRecordController',
    ],
    'aliases' => [
      // Overrides
      'VuFind\Controller\ContentController' => 'Fiddk\Controller\ContentController',
      //'VuFind\Controller\SearchController' => 'fiddk\Controller\SearchController'
    ]
  ],
  'vufind' => [
      'recorddriver_tabs' => [
        'Fiddk\RecordDriver\SolrEdm' => [
          'tabs' => [
            'Description' => null,
            'Similar' => 'SimilarItemsCarousel',
            'Details' => 'StaffViewArray',
          ]
        ],
      ],
    ],
];

// Define record view routes -- route name => controller
$recordRoutes = [
    'record' => 'Record',
    'collection' => 'Collection',
    'missingrecord' => 'MissingRecord',
    'solrauthrecord' => 'Authority'
];

$staticRoutes = [
        'Search/AuthorityResults'
];

$routeGenerator = new \VuFind\Route\RouteGenerator();
$routeGenerator->addRecordRoutes($config, $recordRoutes);
$routeGenerator->addStaticRoutes($config, $staticRoutes);

// Add the home route last
$config['router']['routes']['home'] = [
    'type' => 'Zend\Router\Http\Literal',
    'options' => [
        'route'    => '/',
        'defaults' => [
            'controller' => 'index',
            'action'     => 'Home',
        ]
    ]
];

return $config;
