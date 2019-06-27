<?php
/**
 * Author aspect of the Search Multi-class (Params)
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
 * @package  Search_SolrAuthor
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace Fiddk\Search\SolrAuthority;

/**
 * Author Search Options
 *
 * @category VuFind
 * @package  Search_SolrAuthor
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class Params extends \VuFind\Search\Solr\Params
{
  /**
     * Support method for _initSearch() -- handle basic settings.
     *
     * @param \Zend\StdLib\Parameters $request Parameter object representing user
     * request.
     *
     * @return bool True if search settings were found, false if not.
     */
    protected function initBasicSearch($request)
    {
        // We need an id and a type to search accordingly
        if (null === ($lookfor = $request->get('id'))) {
            return false;
        }
        if (null === ($type = $request->get('type'))) {
            return false;
        }
        // Set the correct search handler depending on authority type:
        if ($type == 'Agent') {
          $this->setBasicSearch($lookfor, 'Agent');
        } else {
          $this->setBasicSearch($lookfor, 'Event');
        }
        return true;
    }

    /**
     * Build a string for onscreen display showing the
     *   query used in the search (not the filters).
     *
     * @return string user friendly version of 'query'
     */
     public function getDisplayQuery()
     {
       // For display purposes, find a nice way of displaying authority queries
       $q = parent::getDisplayQuery();
       if (strpos($q,'gnd_') === 0) {
         $q = '(GND ' . substr($q,4) . ')';
       } else {
         $q = '';
       }
       return $q;
}
}
