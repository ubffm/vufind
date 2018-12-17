<?php
/**
 * Default Controller.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 *
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link     https://vufind.org Main Site
 */
namespace Fiddk\Controller;
use \VuFind\Db\AdapterFactory;
use \Zend\Db\Adapter\Adapter;
use \Zend\Db\Sql\Sql;

/**
 * Redirects the user to the appropriate default VuFind action.
 *
 * @category VuFind
 *
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link     https://vufind.org Main Site
 */
class SearchController extends \VuFind\Controller\SearchController
{
    /**
     * Results action for authority records.
     *
     * @return mixed
     */
    public function authorityresultsAction()
    {
        $id = $this->params()->fromQuery('lookfor');
        $driver = $this->serviceLocator->get('VuFind\RecordLoader')
            ->load($id, 'SolrAuth');

        // Special case -- redirect tag searches.
        $tag = $this->params()->fromQuery('tag');
        if (!empty($tag)) {
            $query = $this->getRequest()->getQuery();
            $query->set('lookfor', $tag);
            $query->set('type', 'tag');
        }
        if ($this->params()->fromQuery('type') == 'tag') {
            // Because we're coming in from a search, we want to do a fuzzy
            // tag search, not an exact search like we would when linking to a
            // specific tag name.
            $query = $this->getRequest()->getQuery()->set('fuzzy', 'true');

            return $this->forwardTo('Tag', 'Home');
        }
        $view = $this->createViewModel(['driver' => $driver]);

        // Handle saved search requests:
        $savedId = $this->params()->fromQuery('saved', false);
        if ($savedId !== false) {
            return $this->redirectToSavedSearch($savedId);
        }

        $runner = $this->serviceLocator->get('VuFind\SearchRunner');

        // Send both GET and POST variables to search class:
        $request = $this->getRequest()->getQuery()->toArray()
            + $this->getRequest()->getPost()->toArray();

        $lastView = $this->getSearchMemory()
            ->retrieveLastSetting($this->searchClassId, 'view');
        $view->results = $results = $runner->run(
            $request, $this->searchClassId, $this->getSearchSetupCallback(),
            $lastView
        );
        $view->params = $results->getParams();

        // If we received an EmptySet back, that indicates that the real search
        // failed due to some kind of syntax error, and we should display a
        // warning to the user; otherwise, we should proceed with normal post-search
        // processing.
        if ($results instanceof \VuFind\Search\EmptySet\Results) {
            $view->parseError = true;
        } else {
            // If a "jumpto" parameter is set, deal with that now:
            if ($jump = $this->processJumpTo($results)) {
                return $jump;
            }

            // Remember the current URL as the last search.
            $this->rememberSearch($results);

            // Add to search history:
            if ($this->saveToHistory) {
                $this->saveSearchToHistory($results);
            }

            // Set up results scroller:
            if ($this->resultScrollerActive()) {
                $this->resultScroller()->init($results);
            }
        }

        // Special case: If we're in RSS view, we need to render differently:
        if (isset($view->params) && $view->params->getView() == 'rss') {
            $response = $this->getResponse();
            $response->getHeaders()->addHeaderLine('Content-type', 'text/xml');
            $feed = $this->getViewRenderer()->plugin('resultfeed');
            $response->setContent($feed($view->results)->export('rss'));
            return $response;
        }

        // Search toolbar
        $config = $this->serviceLocator->get('VuFind\Config')->get('config');
        $view->showBulkOptions = isset($config->Site->showBulkOptions)
          && $config->Site->showBulkOptions;

        return $view;
    }

    /**
     * Home action.
     *
     * @return mixed
     */
    public function homeAction()
    {
        $view = parent::homeAction();
        $view->options = $this->serviceLocator
            ->get('VuFind\SearchOptionsPluginManager')->get($this->searchClassId);
        $specialFacets = $this->parseSpecialFacetsSetting(
           $view->options->getSpecialAdvancedFacets()
        );
        $view->checkboxFacets = $this->processAdvancedCheckboxes(
          $specialFacets['checkboxes'], $view->saved
        );

        // news
        $db = new AdapterFactory($this->getConfig());
        $db = $db->getAdapter();
        $view->newslist = $db->query(
                'SELECT * from news WHERE pin!=true AND active=true  AND startdate<=NOW() AND enddate>=NOW() ORDER BY startdate DESC;' , Adapter::QUERY_MODE_EXECUTE
                );
        $view->pinnedlist = $db->query(
                'SELECT * from news WHERE active=true AND pin=true AND startdate<=NOW() AND enddate>=NOW() ORDER BY startdate DESC;' , Adapter::QUERY_MODE_EXECUTE
                );

        // random records for home page from result set with thumbnails
        $runner = $this->serviceLocator->get('VuFind\SearchRunner');
        $request = ['lookfor' => true,'type'=> 'hasThumb'];
        $view->results = $runner->run($request, $this->searchClassId, $this->getSearchSetupCallback());

        return $view;
    }
}
