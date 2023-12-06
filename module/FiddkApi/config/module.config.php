<?php

namespace FiddkApi\Module\Configuration;
return [
  'controllers' => 
  [
    'factories' => 
    [
      'FiddkApi\Controller\SearchApiController' => 'VuFindApi\Controller\SearchApiControllerFactory',
      'FiddkApi\Controller\PersonApiController' => 'FiddkApi\Controller\PersonApiControllerFactory',
      'FiddkApi\Controller\CorporationApiController' => 'FiddkApi\Controller\CorporationApiControllerFactory',
      'FiddkApi\Controller\EventApiController' => 'FiddkApi\Controller\EventApiControllerFactory',
      'FiddkApi\Controller\WorkApiController' => 'FiddkApi\Controller\WorkApiControllerFactory',
      'VuFindApi\Controller\ApiController' => 'FiddkApi\Controller\ApiControllerFactory',
    ],
    'aliases' => 
    [
      'PersonApi' => 'FiddkApi\Controller\PersonApiController',
      'CorporationApi' => 'FiddkApi\Controller\CorporationApiController',
      'EventApi' => 'FiddkApi\Controller\EventApiController',
      'WorkApi' => 'FiddkApi\Controller\WorkApiController',
      'VuFindApi\Controller\SearchApiController' => 'FiddkApi\Controller\SearchApiController',

      'personapi' => 'PersonApi',
      'corporationapi' => 'CorporationApi',
      'eventapi' => 'EventApi',
      'workapi' => 'WorkApi',
    ],
  ],
  'service_manager' => [
    'factories' => [
        'FiddkApi\Formatter\PersonRecordFormatter' => 'FiddkApi\Formatter\PersonRecordFormatterFactory',
        'FiddkApi\Formatter\CorporationRecordFormatter' => 'FiddkApi\Formatter\CorporationRecordFormatterFactory',
        'FiddkApi\Formatter\EventRecordFormatter' => 'FiddkApi\Formatter\EventRecordFormatterFactory',
        'FiddkApi\Formatter\WorkRecordFormatter' => 'FiddkApi\Formatter\WorkRecordFormatterFactory',
    ],
  ],
  'vufind_api' => [
    'register_controllers' => [
        \FiddkApi\Controller\PersonApiController::class,
        \FiddkApi\Controller\CorporationApiController::class,
        \FiddkApi\Controller\EventApiController::class,
        \FiddkApi\Controller\WorkApiController::class,
    ],
  ],
  'router' => [
    'routes' => [      
      'personApiv1' => [
        'type' => 'Laminas\Router\Http\Literal',
        'verb' => 'get,post,options',
        'options' => [
          'route'    => '/api/v1/person',
          'defaults' => [
            'controller' => 'PersonApi',
            'action'     => 'record',
          ],
        ],
      ],
      'personsearchApiv1' => [
        'type' => 'Laminas\Router\Http\Literal',
        'verb' => 'get,post,options',
        'options' => [
            'route'    => '/api/v1/personsearch',
            'defaults' => [
                'controller' => 'PersonApi',
                'action'     => 'search',
            ],
        ],
      ],
      'corporationApiv1' => [
        'type' => 'Laminas\Router\Http\Literal',
        'verb' => 'get,post,options',
        'options' => [
          'route'    => '/api/v1/corporation',
          'defaults' => [
            'controller' => 'CorporationApi',
            'action'     => 'record',
          ],
        ],
      ],
      'corporationsearchApiv1' => [
        'type' => 'Laminas\Router\Http\Literal',
        'verb' => 'get,post,options',
        'options' => [
            'route'    => '/api/v1/corporationsearch',
            'defaults' => [
                'controller' => 'CorporationApi',
                'action'     => 'search',
            ],
        ],
      ],
      'eventApiv1' => [
        'type' => 'Laminas\Router\Http\Literal',
        'verb' => 'get,post,options',
        'options' => [
          'route'    => '/api/v1/event',
          'defaults' => [
            'controller' => 'EventApi',
            'action'     => 'record',
          ],
        ],
      ],
      'eventsearchApiv1' => [
        'type' => 'Laminas\Router\Http\Literal',
        'verb' => 'get,post,options',
        'options' => [
            'route'    => '/api/v1/eventsearch',
            'defaults' => [
                'controller' => 'EventApi',
                'action'     => 'search',
            ],
        ],
      ],
      'workApiv1' => [
        'type' => 'Laminas\Router\Http\Literal',
        'verb' => 'get,post,options',
        'options' => [
          'route'    => '/api/v1/work',
          'defaults' => [
            'controller' => 'workApi',
            'action'     => 'record',
          ],
        ],
      ],
      'worksearchApiv1' => [
        'type' => 'Laminas\Router\Http\Literal',
        'verb' => 'get,post,options',
        'options' => [
            'route'    => '/api/v1/worksearch',
            'defaults' => [
                'controller' => 'WorkApi',
                'action'     => 'search',
            ],
        ],
      ],
    ],
  ],

];