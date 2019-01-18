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
      ],
  ],
  'controllers' => [
    'factories' => [
      'Fiddk\Controller\ContentController' => 'VuFind\Controller\AbstractBaseFactory',
      'Fiddk\Controller\DataProviderController' => 'VuFind\Controller\AbstractBaseFactory',
      'Fiddk\Controller\AuthorityController' => 'VuFind\Controller\AbstractBaseFactory',
      'Fiddk\Controller\RecordController' => 'VuFind\Controller\AbstractBaseWithConfigFactory',
      'Fiddk\Controller\SearchController' => 'VuFind\Controller\AbstractBaseWithConfigFactory'
      //'record' => 'fiddk\Controller\Factory::getRecordController',
    ],
    'aliases' => [
      'DataProvider' => 'Fiddk\Controller\DataProviderController',
      'dataprovider' => 'Fiddk\Controller\DataProviderController',

      // Overrides
      'VuFind\Controller\ContentController' => 'Fiddk\Controller\ContentController',
      'VuFind\Controller\RecordController' => 'Fiddk\Controller\RecordController',
      'VuFind\Controller\AuthorityController' => 'Fiddk\Controller\AuthorityController',
      'VuFind\Controller\SearchController' => 'Fiddk\Controller\SearchController',

      //'record' => 'Fiddk\Controller\RecordController',
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
          'Fiddk\Recommend\AuthorityInfo' => 'Fiddk\Recommend\Factory::getAuthorityInfo',
        ],
        'aliases' => [
          'authorityinfo' => 'Fiddk\Recommend\AuthorityInfo',
        ],
      ],
      'search_options' => [
        'factories' => [
          'Fiddk\Search\SolrAuthorityInfo\Options' => 'VuFind\Search\Options\OptionsFactory',
          ],
        'aliases' => [
          'solrauthorityinfo' => 'Fiddk\Search\SolrAuthorityInfo\Options',
          ],
      ],
      'search_params' => [
        'factories' => [
          'Fiddk\Search\SolrAuthorityInfo\Params' => 'VuFind\Search\Solr\ParamsFactory',
          ],
        'aliases' => [
          'solrauthorityinfo' => 'Fiddk\Search\SolrAuthorityInfo\Params',
          ],
      ],
      'search_results' => [
        'factories' => [
          'Fiddk\Search\SolrAuthorityInfo\Results' => 'VuFind\Search\Solr\ResultsFactory',
          ],
        'aliases' => [
          'solrauthorityinfo' => 'Fiddk\Search\SolrAuthorityInfo\Results',
          ],
      ],
      'recorddriver' => [
        'factories' => [
          'Fiddk\RecordDriver\SolrEdm' =>
              'VuFind\RecordDriver\SolrDefaultFactory',
          'Fiddk\RecordDriver\SolrAuthEdm' =>
              'VuFind\RecordDriver\SolrDefaultWithoutSearchServiceFactory',
        ],
        'aliases' => [
          'solredm' => 'Fiddk\RecordDriver\SolrEdm',
          'solrauthedm' => 'Fiddk\RecordDriver\SolrAuthEdm',
          'solrauth' => 'Fiddk\RecordDriver\SolrAuthEdm',
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
