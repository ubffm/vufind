<?php
/**
 * Agent "Record" Controller (which is a Search Controller with Recommendation)
 *
 * PHP version 7
 *
 * Copyright (C) Villanova University 2018.
 * Copyright (C) Frankfurt University Library 2019.
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
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace Fiddk\Controller;
use Laminas\Config\Config;
use Laminas\ServiceManager\ServiceLocatorInterface;
/**
 * Agent "Record" Controller (which is a Search Controller with Recommendation)
 *
 * @category VuFind
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class AgentController extends \VuFind\Controller\AbstractSearch
{
    protected $driver = null;

    public function homeAction()
    {
      $this->searchClassId = 'SolrAuthor';
      $this->driver = $this->loadRecord();
      $id = $this->driver->getUniqueID();
      $name = $this->driver->getTitle();
      $this->searchClassId = 'SolrAuthority';
      $query = $this->getRequest()->getQuery();
      $query->set('id', $id);
      $query->set('type', 'Agent');
      $query->set('name', $name);
      return !empty($id) ?
        $this->forwardTo('Agent', 'Results') : $this->forwardTo('Search', 'Home');
    }
    /**
     * Load the record requested by the user; note that this is not done in the
     * init() method since we don't want to perform an expensive search twice
     * when homeAction() forwards to another method.
     *
     * @return AbstractRecordDriver
     */
    protected function loadRecord()
    {
        // Only load the record if it has not already been loaded.  Note that
        // when determining record ID, we check both the route match (the most
        // common scenario) and the GET parameters (a fallback used by some
        // legacy routes).
        if (!is_object($this->driver)) {
            $recordLoader = $this->getRecordLoader();
            $cacheContext = $this->getRequest()->getQuery()->get('cacheContext');
            if (isset($cacheContext)) {
                $recordLoader->setCacheContext($cacheContext);
            }
            $this->driver = $recordLoader->load(
                $this->params()->fromRoute('id', $this->params()->fromQuery('id')),
                $this->searchClassId,
                false
            );
        }
        return $this->driver;
    }

    /**
    * Send search results to results view
    *
    * @return \Laminas\View\Model\ViewModel
    */
    public function resultsAction()
    {
      $view = $this->createViewModel(['driver' => $this->driver]);

      // Handle saved search requests:
      $savedId = $this->params()->fromQuery('saved', false);
      if ($savedId !== false) {
        return $this->redirectToSavedSearch($savedId);
      }
      $runner = $this->serviceLocator->get(\VuFind\Search\SearchRunner::class);
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
      $config = $this->serviceLocator->get(\VuFind\Config\PluginManager::class)
        ->get('config');
      $view->showBulkOptions = isset($config->Site->showBulkOptions)
        && $config->Site->showBulkOptions;
      return $view;
    }

}
