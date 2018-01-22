<?php
namespace fiddk\Module\Configuration;

$config = [
  'controllers' => [
    'factories' => [
      'dprovider' => 'fiddk\Controller\Factory::getDproviderController',
      'spages' => 'fiddk\Controller\Factory::getSpagesController',
      'search' => 'fiddk\Controller\Factory::getSearchController',
      'ajax' => 'fiddk\Controller\Factory::getAjaxController',
      'feedback' => 'fiddk\Controller\Factory::getFeedbackController',
      'record' => 'fiddk\Controller\Factory::getRecordController',
    ],
  ],
  'vufind' => [
    'plugin_managers' => [
      'recorddriver' => [
        'factories' => [
          'solredm' => 'fiddk\RecordDriver\Factory::getSolrEdm',
          'solrauth' => 'fiddk\RecordDriver\Factory::getSolrAuth',
        ],
      ],
    ],
      'recorddriver_tabs' => [
        'fiddk\RecordDriver\SolrEdm' => [
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
        'spages/uber',
        'spages/beirat',
        'spages/netzwerke',
        'spages/netzwerke2',
        'spages/netzwerke3',
        'spages/dokumentation',
        'spages/trailer',
        'spages/downloads',
        'spages/kontakt',
        'spages/suchen',
        'spages/faq',
        'spages/news',
        'spages/themen',
        'spages/contents',
        'spages/neuerwerb',
        'spages/fernleihe',
        'spages/licensed',
        'spages/kaufvorschlag',
        'spages/impressum',
        'spages/copyright',
        'spages/verweise',
        'spages/datenschutz',
        'spages/haftung',
        'Search/AuthorityResults'
];

$routeGenerator = new \VuFind\Route\RouteGenerator();
$routeGenerator->addRecordRoutes($config, $recordRoutes);
$routeGenerator->addStaticRoutes($config, $staticRoutes);

// Add the home route last
$config['router']['routes']['home'] = [
    'type' => 'Zend\Mvc\Router\Http\Literal',
    'options' => [
        'route'    => '/',
        'defaults' => [
            'controller' => 'index',
            'action'     => 'Home',
        ]
    ]
];

return $config;
