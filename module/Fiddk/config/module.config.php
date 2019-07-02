<?php
namespace Fiddk\Module\Configuration;

$config = [
  'router' => [
      'routes' => [
          'dataprovider-page' => [
              'type'    => 'Zend\Router\Http\Segment',
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
            'type' => 'Zend\Router\Http\Literal',
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
          'Fiddk\Recommend\AgentInfo' => 'Fiddk\Recommend\AgentInfoFactory',
          'Fiddk\Recommend\EventInfo' => 'Fiddk\Recommend\EventInfoFactory',
        ],
        'aliases' => [
          'agentinfo' => 'Fiddk\Recommend\AgentInfo',
          'eventinfo' => 'Fiddk\Recommend\EventInfo',
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
      'recorddriver' => [
        'factories' => [
          'Fiddk\RecordDriver\SolrEdm' =>
              'VuFind\RecordDriver\SolrDefaultFactory',
          'Fiddk\RecordDriver\SolrAuthor' =>
              'VuFind\RecordDriver\SolrDefaultFactory',
          'Fiddk\RecordDriver\SolrEvent' =>
              'VuFind\RecordDriver\SolrDefaultFactory',

        ],
        'aliases' => [
          'solredm' => 'Fiddk\RecordDriver\SolrEdm',
          'solrauthor' => 'Fiddk\RecordDriver\SolrAuthor',
          'solrevent' => 'Fiddk\RecordDriver\SolrEvent',
        ],
      ],
      'recordtab' => [
        'factories' => [
          'Fiddk\RecordTab\StaffViewEdm' => 'Zend\ServiceManager\Factory\InvokableFactory',
        ],
        'aliases' => [
          'staffviewedm' => 'Fiddk\RecordTab\StaffViewEdm',
        ],
      ],
    ],
    'recorddriver_tabs' => [
      'Fiddk\RecordDriver\SolrEdm' => [
        'tabs' => [
          'TOC' => 'TOC',
          'Similar' => 'SimilarItemsCarousel',
          'Details' => 'StaffViewEdm',
        ],
        'defaultTab' => null,
        ],
      'Fiddk\RecordDriver\SolrEvent' => [
        'tabs' => [
        ],
        'defaultTab' => null,
        ],
      'Fiddk\RecordDriver\SolrAuthor' => [
        'tabs' => [
        ],
        'defaultTab' => null,
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
                 'AgentSearch/Home','AgentSearch/Results'];

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
