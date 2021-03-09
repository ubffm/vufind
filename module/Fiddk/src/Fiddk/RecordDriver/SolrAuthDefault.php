<?php
/**
 * Model for EDM authority records in Solr.
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
class SolrAuthDefault extends \VuFind\RecordDriver\SolrAuthDefault
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
   * Returns the thumbnail url or []
   */
  public function getThumbnail($size = 'small')
  {
    return isset($this->fields['thumbnail'])
        ? $this->fields['thumbnail'] : [];
  }

  /**
   * Returns the further source links (Personal Name, Corporate Name or Event)
   */
  public function getSource()
  {
    $sources = [];
    if (isset($this->fields['source'])) {
      foreach ($this->fields['source'] as $source) {
        $sources[] = ["id" => $source, "name" => "Theaterlexikon der Schweiz"];
      }
    }
    return $sources;
  }

  public function getInstitution() {
    return isset($this->fields['institution']) ? $this->fields['institution'] : '';
  }

  public function getIntermediate() {
    return isset($this->fields['intermediate']) ? $this->fields['intermediate'] : '';
  }

  /**
   * Get an array of all dataproviders, also taking in consideration if
   * there are intermediate data providers.
   *
   * @return array
   */
  public function getInstitutionLinked() {
    $dprovConf = $this->mainConfig->DataProvider;
    $inter = $this->getIntermediate();
    $inst = $this->getInstitution();
    $res = [];
    if (!empty($inter)) {
      $type = "inter";
      $instkey = preg_replace("/\r|\n|\s|,|\/|\(|\)/", "", $inst);
      $info = explode(',',$dprovConf[$instkey]);
      $instlink = $info[0];
      $instid = $info[1];
      $interkey = preg_replace("/\r|\n|\s|,|\/|\(|\)/", "", $inter);
      $info = explode(',',$dprovConf[$interkey]);
      $interlink = $info[0];
      $res = [$type => [$inter,$interlink,$instid,$inst,$instlink]];
    } else {
      $type = "inst";
      $instkey = preg_replace("/\r|\n|\s|,|\/|\(|\)/", "", $inst);
      $info = explode(',',$dprovConf[$instkey]);
      $res = [$type => [$inst,$info[0],$info[1]]];
    }
    return $res;
  }

}
