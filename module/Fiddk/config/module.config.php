<?php
namespace Fiddk\Module\Config;

$config = [
  'router' => [
      'routes' => [
          'dataprovider-page' => [
              'type'    => 'Laminas\Router\Http\Segment',
              'options' => [
                  'route'    => '/DataProvider/[:page]',
                  'constraints' => [
                      'page'     => '[a-zA-Z][a-zA-Z_-]*',
                  ],
                  'defaults' => [
                      'controller' => 'DataProvider',
                      'action'     => 'DataProvider',
                  ]
              ],
          ],
          'showcase' => [
            'type' => 'Laminas\Router\Http\Literal',
            'options' => [
              'route'    => '/Showcase',
            'defaults' => [
              'controller' => 'Showcase',
              'action'     => 'Home',
            ]
          ]
],
      ],
  ],
  'controllers' => [
    'factories' => [
      'Fiddk\Controller\ContentController' => 'VuFind\Controller\AbstractBaseFactory',
      'Fiddk\Controller\DataProviderController' => 'VuFind\Controller\AbstractBaseFactory',
      'Fiddk\Controller\AgentSearchController' => 'VuFind\Controller\AbstractBaseFactory',
      'Fiddk\Controller\AgentController' => 'VuFind\Controller\AbstractBaseWithConfigFactory',
      'Fiddk\Controller\RecordController' => 'VuFind\Controller\AbstractBaseWithConfigFactory',
      'Fiddk\Controller\EventSearchController' => 'VuFind\Controller\AbstractBaseFactory',
      'Fiddk\Controller\EventController' => 'VuFind\Controller\AbstractBaseWithConfigFactory',
      'Fiddk\Controller\WorkSearchController' => 'VuFind\Controller\AbstractBaseFactory',
      'Fiddk\Controller\WorkController' => 'VuFind\Controller\AbstractBaseFactory',
      'Fiddk\Controller\SearchController' => 'VuFind\Controller\AbstractBaseWithConfigFactory',
      'Fiddk\Controller\FeedbackController' => 'VuFind\Controller\AbstractBaseFactory',
      'Fiddk\Controller\ShowcaseController' => 'VuFind\Controller\AbstractBaseFactory',
    ],
    'aliases' => [
      'DataProvider' => 'Fiddk\Controller\DataProviderController',
      'dataprovider' => 'Fiddk\Controller\DataProviderController',
      'Showcase' => 'Fiddk\Controller\ShowcaseController',
      'showcase' => 'Fiddk\Controller\ShowcaseController',
      'AgentSearch' => 'Fiddk\Controller\AgentSearchController',
      'agentsearch' => 'Fiddk\Controller\AgentSearchController',
      'Agent' => 'Fiddk\Controller\AgentController',
      'agent' => 'Fiddk\Controller\AgentController',
      'EventSearch' => 'Fiddk\Controller\EventSearchController',
      'eventsearch' => 'Fiddk\Controller\EventSearchController',
      'Event' => 'Fiddk\Controller\EventController',
      'event' => 'Fiddk\Controller\EventController',
      'WorkSearch' => 'Fiddk\Controller\WorkSearchController',
      'worksearch' => 'Fiddk\Controller\WorkSearchController',
      'Work' => 'Fiddk\Controller\WorkController',
      'work' => 'Fiddk\Controller\WorkController',
      'Feedback' => 'Fiddk\Controller\FeedbackController',
      'feedback' => 'Fiddk\Controller\FeedbackController',

      // Overrides
      'VuFind\Controller\ContentController' => 'Fiddk\Controller\ContentController',
      'VuFind\Controller\RecordController' => 'Fiddk\Controller\RecordController',
      'VuFind\Controller\AuthorController' => 'Fiddk\Controller\AgentSearchController',
      'VuFind\Controller\SearchController' => 'Fiddk\Controller\SearchController',
      'VuFind\Controller\FeedbackController' => 'Fiddk\Controller\FeedbackController',
    ]
  ],
  'service_manager' => [
    'allow_override' => true,
    'factories' => [
      'Fiddk\ContentBlock\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
      'Fiddk\RecordDriver\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
      'Fiddk\Search\Options\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
      'Fiddk\Search\Params\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
      'Fiddk\Search\Results\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
    ],
    'aliases' => [
      'VuFind\ContentBlock\PluginManager' => 'Fiddk\ContentBlock\PluginManager',
      'VuFind\RecordDriver\PluginManager' => 'Fiddk\RecordDriver\PluginManager',
      'VuFind\Search\Options\PluginManager' => 'Fiddk\Search\Options\PluginManager',
      'VuFind\Search\Params\PluginManager' => 'Fiddk\Search\Params\PluginManager',
      'VuFind\Search\Results\PluginManager' => 'Fiddk\Search\Results\PluginManager',

    ],
  ],
  'vufind' => [
    'plugin_managers' => [
      'autocomplete' => [
        'factories' => [
          'Fiddk\Autocomplete\SolrAuthor' => 'VuFind\Autocomplete\SolrFactory',
          'Fiddk\Autocomplete\SolrEvent' => 'VuFind\Autocomplete\SolrFactory',
        ],
        'aliases' => [
          'solrauthor' => 'Fiddk\Autocomplete\SolrAuthor',
          'solrevent' => 'Fiddk\Autocomplete\SolrEvent',
        ],
      ],
      'recommend' => [
        'factories' => [
          'Fiddk\Recommend\AuthInfo' => 'VuFind\Recommend\AuthorInfoFactory',
        ],
        'aliases' => [
          'authinfo' => 'Fiddk\Recommend\AuthInfo',
        ],
      ],
      'search_options' => [ /* See Fiddk\Search\Options\PluginManager for defaults */ ],
      'search_params' => [ /* See Fiddk\Search\Params\PluginManager for defaults */ ],
      'search_results' => [ /* See Fiddk\Search\Results\PluginManager for defaults */ ],
      'search_backend' => [
        'factories' => [
          'SolrEvent' => 'Fiddk\Search\Factory\SolrEventBackendFactory',
          'SolrAuthor' => 'Fiddk\Search\Factory\SolrAuthorBackendFactory',
          'SolrWork' => 'Fiddk\Search\Factory\SolrWorkBackendFactory',
        ],
      ],
      'recordtab' => [
        'aliases' => [
          'staffviewedm' => 'Fiddk\RecordTab\StaffViewEdm',
        ],
      ],
    ],
    ],
];

// Define record view routes -- route name => controller
$recordRoutes = [
    'record' => 'Record',
    'eventrecord' => 'event',
    'solreventrecord' => 'event',
    'collection' => 'Collection',
    'missingrecord' => 'MissingRecord',
    'agentrecord' => 'agent',
    'solrauthorrecord' => 'agent',
    'workrecord' => 'work',
    'solrworkrecord' => 'work',
];

$staticRoutes = ['EventSearch/Home','EventSearch/Results',
                 'Event/FacetList','AgentSearch/Home',
                 'AgentSearch/Results','Agent/FacetList',
                 'WorkSearch/Home','WorkSearch/Results',
                 'Work/FacetList','Showcase/Home',
                 'Showcase/Playbills'];

$routeGenerator = new \VuFind\Route\RouteGenerator();
$routeGenerator->addRecordRoutes($config, $recordRoutes);
$routeGenerator->addStaticRoutes($config, $staticRoutes);

// Add the home route last
$config['router']['routes']['home'] = [
    'type' => 'Laminas\Router\Http\Literal',
    'options' => [
        'route'    => '/',
        'defaults' => [
            'controller' => 'index',
            'action'     => 'Home',
        ]
    ]
];

return $config;
