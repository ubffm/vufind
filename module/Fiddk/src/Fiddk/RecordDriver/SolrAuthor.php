<?php
/**
 * Model for EDM authority records in Solr.
 *
 * PHP version 5
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
 * Model for EDM authority records in Solr.
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
class SolrAuthor extends \VuFind\RecordDriver\SolrAuthDefault
{
  use EdmReaderTrait;

  /**
   * Returns the authority type (Personal Name, Corporate Name or Event)
   */
  public function getAuthType()
  {
     return isset($this->fields['record_type'])
           ? $this->fields['record_type'] : '';
  }

  /**
  * Get the occupation of the person.
  *
  * @return array
  */
  public function getOccupation()
  {
     return isset($this->fields['occupation'])
         ? $this->fields['occupation'] : [];
  }

  /**
   * Get domain of organization.
   *
   * @return array
   */
  public function getOrgaDomain()
  {
     return isset($this->fields['orga_domain'])
         ? $this->fields['orga_domain'] : [];
  }

  /**
   * Get birth date of person or date of establishment of organization.
   *
   * @return string
   */
  public function getBirthDate()
  {
     return isset($this->fields['birth_date'])
         ? $this->fields['birth_date'] : "";
  }

  /**
   * Get death date of person or date of termination of organization.
   *
   * @return string
   */
  public function getDeathDate()
  {
     return isset($this->fields['death_date'])
         ? $this->fields['death_date'] : "";
  }

  /**
   * Returns the thumbnail url or []
   */
  public function getThumbnail($size = 'small')
  {
    return isset($this->fields['thumbnail'])
        ? $this->fields['thumbnail'] : [];
  }

}
