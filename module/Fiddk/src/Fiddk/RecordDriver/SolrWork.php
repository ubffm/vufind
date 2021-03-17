<?php
/**
 * Model for EDM work authority records in Solr.
 *
 * PHP version 7
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
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
namespace Fiddk\RecordDriver;

/**
 * Model for EDM work authority records in Solr.
 *
 * @category VuFind
 *
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
class SolrWork extends SolrEdm
{
  /**
   * Get type
   *
   * @return array
   */
  public function getAuthType()
  {
      return "Work";
  }

  /**
   * Get the number of event records belonging to this work
   *
   * @return int Number of records
   */
  public function getEventCount()
  {

      $id = $this->getUniqueId();
      $query = new \VuFindSearch\Query\Query(
          'work_id:"' . $id . '"'
      );
      // Disable highlighting for efficiency; not needed here:
      $params = new \VuFindSearch\ParamBag(['hl' => ['false']]);
      return $this->searchService
          ->search("SolrEvent", $query, 0, 0, $params)
          ->getTotal();
  }

}
