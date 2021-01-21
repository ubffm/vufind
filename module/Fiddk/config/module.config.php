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
      'Fiddk\Controller\RecordController' => 'VuFind\Controller\AbstractBaseWithConfigFactory',
      'Fiddk\Controller\EventSearchController' => 'VuFind\Controller\AbstractBaseFactory',
      'Fiddk\Controller\EventController' => 'VuFind\Controller\AbstractBaseFactory',
      'Fiddk\Controller\AgentController' => 'VuFind\Controller\AbstractBaseWithConfigFactory',
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
    ],
    'aliases' => [
      'VuFind\ContentBlock\PluginManager' => 'Fiddk\ContentBlock\PluginManager',
      'VuFind\RecordDriver\PluginManager' => 'Fiddk\RecordDriver\PluginManager',
    ],
  ],
  'vufind' => [
    'plugin_managers' => [
      'ajaxhandler' => [
        'factories' => [
          'Fiddk\AjaxHandler\GetTotalResults' =>
              'Fiddk\AjaxHandler\GetTotalResultsFactory',
                ],
        'aliases' => [
          'GetTotalResults' => 'Fiddk\AjaxHandler\GetTotalResults',
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
      'search_backend' => [
        'factories' => [
          'SolrEvent' => 'Fiddk\Search\Factory\SolrEventBackendFactory',
          'SolrAuthor' => 'Fiddk\Search\Factory\SolrAuthorBackendFactory',
        ],
      ],
      'search_options' => [
        'factories' => [
          'Fiddk\Search\SolrAuthority\Options' => 'VuFind\Search\Options\OptionsFactory',
          'Fiddk\Search\SolrAuthor\Options' => 'VuFind\Search\Options\OptionsFactory',
          'Fiddk\Search\SolrEvent\Options' => 'VuFind\Search\Options\OptionsFactory',
          ],
        'aliases' => [
          'solrauthority' => 'Fiddk\Search\SolrAuthority\Options',
          'VuFind\Search\SolrAuthor\Options' => 'Fiddk\Search\SolrAuthor\Options',
          'solrevent' => 'Fiddk\Search\SolrEvent\Options',
          ],
      ],
      'search_params' => [
        'factories' => [
          'Fiddk\Search\SolrEvent\Params' => 'VuFind\Search\Solr\ParamsFactory',
          'Fiddk\Search\SolrAuthority\Params' => 'VuFind\Search\Solr\ParamsFactory',
          'Fiddk\Search\SolrAuthor\Params' => 'VuFind\Search\Solr\ParamsFactory',
          ],
        'aliases' => [
          'solrauthority' => 'Fiddk\Search\SolrAuthority\Params',
          'VuFind\Search\SolrAuthor\Params' => 'Fiddk\Search\SolrAuthor\Params',
          'solrevent' => 'Fiddk\Search\SolrEvent\Params',
          ],
      ],
      'search_results' => [
        'factories' => [
          'Fiddk\Search\SolrAuthority\Results' => 'VuFind\Search\Solr\ResultsFactory',
          'Fiddk\Search\SolrAuthor\Results' => 'VuFind\Search\Solr\ResultsFactory',
          'Fiddk\Search\SolrEvent\Results' => 'VuFind\Search\Solr\ResultsFactory',
          ],
        'aliases' => [
          'VuFind\Search\SolrAuthor\Results' => 'Fiddk\Search\SolrAuthor\Results',
          'solrevent' => 'Fiddk\Search\SolrEvent\Results',
          'solrauthority' => 'Fiddk\Search\SolrAuthority\Results',
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
];

$staticRoutes = ['EventSearch/Home','EventSearch/Results',
                 'AgentSearch/Home','AgentSearch/Results',
                 'Showcase/Home', 'Showcase/Playbills'];

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
