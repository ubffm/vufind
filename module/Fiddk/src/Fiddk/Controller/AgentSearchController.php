<?php
/**
 * Author Controller
 *
 * PHP version 7
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
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace Fiddk\Controller;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Authority Controller
 *
 * @category VuFind
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class AgentSearchController extends \VuFind\Controller\AuthorController
{
  /**
   * Constructor
   *
   * @param ServiceLocatorInterface $sm Service locator
   */
  public function __construct(ServiceLocatorInterface $sm)
  {
      $this->searchClassId = 'SolrAuthor';
      parent::__construct($sm);
  }

  /**
     * Sets the configuration for performing an author search
     *
     * @return mixed
     */
    public function searchAction()
    {
        return parent::resultsAction();
    }


  /**
    * Displays the proper page for a search action
    *
    * @return mixed
    */
    public function homeAction()
    {
      return $this->forwardTo('AuthorSearch', 'Results');
    }

}
