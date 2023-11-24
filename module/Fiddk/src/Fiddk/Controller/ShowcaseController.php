<?php
namespace Fiddk\Controller;

class ShowcaseController extends \VuFind\Controller\SearchController
{
    // random records for home page from result set with thumbnails
    // group them by institution
    public function playbillsAction()
    {
        $view = $this->createViewModel();
        $runner = $this->serviceLocator->get('VuFind\SearchRunner');
        $date = date('Y-m-d');
        $request = ['lookfor' => $date,'type'=> 'playbills'];
        //$request = ['lookfor' => true,'type'=> 'has_thumb', 'filter' => '{!collapse field=institution}'];
        // expand=true&expand.sort=1425_random asc
        $view->results = $runner->run($request, $this->searchClassId, $this->getSearchSetupCallback());
        return $view;
    }
}
