<?php

namespace Fiddk\Controller;

class ShowcaseController extends \VuFind\Controller\SearchController
{
    // random records for home page from result set with thumbnails
    // group them by institution
    public function homeAction() {

      $view = $this->createViewModel();
      $runner = $this->serviceLocator->get('VuFind\SearchRunner');
      $request = ['lookfor' => true,'type'=> 'has_thumb', 'filter' => '{!collapse field=institution}'];
      $view->results = $runner->run($request, $this->searchClassId, $this->getSearchSetupCallback());
      //var_dump($view->results->getResultTotal());
      // expand=true&expand.sort=1425_random asc
      return $view;

    }

}
