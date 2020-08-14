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
use Zend\Config\Config;
use Zend\ServiceManager\ServiceLocatorInterface;
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

}
