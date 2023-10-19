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
      'VuFindApi\Controller\ApiController' => 'FiddkApi\Controller\ApiControllerFactory',
    ],
    'aliases' => 
    [
      'PersonApi' => 'FiddkApi\Controller\PersonApiController',
      'CorporateApi' => 'FiddkApi\Controller\CorporateApiController',
      'EventApi' => 'FiddkApi\Controller\EventApiController',
      'VuFindApi\Controller\SearchApiController' => 'FiddkApi\Controller\SearchApiController',

      'personapi' => 'PersonApi',
      'corporateapi' => 'CorporateApi',
      'eventapi' => 'EventApi',
    ],
  ],
  'service_manager' => [
    'factories' => [
        'FiddkApi\Formatter\PersonRecordFormatter' => 'FiddkApi\Formatter\PersonRecordFormatterFactory',
        'FiddkApi\Formatter\CorporateRecordFormatter' => 'FiddkApi\Formatter\CorporateRecordFormatterFactory',
        'FiddkApi\Formatter\EventRecordFormatter' => 'FiddkApi\Formatter\EventRecordFormatterFactory',
    ],
  ],
  'vufind_api' => [
    'register_controllers' => [
        \FiddkApi\Controller\PersonApiController::class,
        \FiddkApi\Controller\EventApiController::class,
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
                'controller' => 'SearchApi',
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
    ],
  ],

];