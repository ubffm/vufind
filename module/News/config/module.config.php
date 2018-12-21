<?php
namespace News\Module\Configuration;

$config = [
  'controllers' => [
    'factories' => [
      'News\Controller\NewsController' => 'VuFind\Controller\AbstractBaseFactory',
    ],
    'aliases' => [
      'news' => 'News\Controller\NewsController',
    ]
  ],
  'vufind' => [
    'plugin_managers' => [
      'auth' => [
        'invokables' => [
          'database' => 'News\Auth\Database',
        ],
      ],
      'db_row' => [
        'factories' => [
          'News\Db\Row\News' => 'VuFind\Db\Row\RowGatewayFactory',
        ],
        'aliases' => [
          'news' => 'News\Db\Row\News',
        ],
      ],
      'db_table' => [
        'factories' => [
          'News\Db\Table\News' => 'VuFind\Db\Table\GatewayFactory',
        ],
        'aliases' => [
          'news' => 'News\Db\Table\News',
        ],
      ],
    ],
  ],
];

$staticRoutes = [
  'news/list',
  'news/edit',
  'news/rss',
  'news/article',
  'news/archive',
  'news/current'
];

$routeGenerator = new \VuFind\Route\RouteGenerator();
//$routeGenerator->addRecordRoutes($config, $recordRoutes);
//$routeGenerator->addDynamicRoutes($config, $dynamicRoutes);
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
