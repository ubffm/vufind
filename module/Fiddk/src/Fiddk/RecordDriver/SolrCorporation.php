<?php
/**
 * Model for EDM authority corporation records in Solr.
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
 * @author  Demian Katz <demian.katz@villanova.edu>
 * @author  Ere Maijala <ere.maijala@helsinki.fi>
 * @author  Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
namespace Fiddk\RecordDriver;

/**
 * Model for EDM authority corporation records in Solr.
 *
 * @category VuFind
 *
 * @author  Demian Katz <demian.katz@villanova.edu>
 * @author  Ere Maijala <ere.maijala@helsinki.fi>
 * @author  Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
class SolrCorporation extends SolrAuthDefault
{

    /**
     * Get domain of organization.
     *
     * @return array
     */
    public function getOrgaDomain()
    {
        return $this->fields['orga_domain'] ?? [];
    }

    /**
     * Get icon for this entity type.
     *
     * @return string
     */
    public function getRecordIcon()
    {
        return 'fa-building';
    }

    /**
     * Get date of establishment of organization.
     *
     * @return array
     */
    public function getBirthDate()
    {
        return $this->fields['birth_date'] ?? '';
    }

    /**
     * Get date of termination of organization.
     *
     * @return array
     */
    public function getDeathDate()
    {
        return $this->fields['death_date'] ?? '';
    }

    /**
     * Get GND establishment date of corporate body
     */
    public function getGndEstablishment()
    {
        return $this->extraDetails['dateOfEstablishment'] ?? [];
    }

        /**
     * Get GND termination date of corporate body
     */
    public function getGndTermination()
    {
        return $this->extraDetails['dateOfTermination'] ?? [];
    }

    /**
     * Get GND broader term instantial of corporate body
     */
    public function getGndBroaderTermInstantial()
    {
        return $this->extraDetails['broaderTermInstantial'] ?? [];
    }

    /**
     * Get GND place of business of corporate body
     */
    public function getGndPlaceOfBusiness()
    {
        return $this->extraDetails['placeOfBusiness'] ?? [];
    }

    /**
     * Get GND spatial area of activity of corporate body
     */
    public function getGndSpatialAreaOfActivity()
    {
        return $this->extraDetails['spatialAreaOfActivity'] ?? [];
    }

    /**
     * Get GND preceding corporate body
     */
    public function getGndPrecedingCorp()
    {
        return $this->extraDetails['precedingCorporateBody'] ?? [];
    }

    /**
     * Get GND succeeding corporate body
     */
    public function getGndSucceedingCorp()
    {
        return $this->extraDetails['succeedingCorporateBody'] ?? [];
    }

    /**
     * Get GND abbreviation of corporate body
     */
    public function getGndAbbreviation()
    {
        return $this->extraDetails['abbreviatedNameForTheCorporateBody'] ?? [];
    }

    /**
     * Get the number of work records related to this agent
     *
     * @return int Number of records
     */
    public function getWorkCount()
    {
        $id = $this->getUniqueId();
        $query = new \VuFindSearch\Query\Query(
            'author_id:"' . $id . '"'
        );
        // Disable highlighting for efficiency; not needed here:
        $params = new \VuFindSearch\ParamBag(['hl' => ['false']]);
        $command = new \VuFindSearch\Command\SearchCommand("SolrWork", $query, 0, 0, $params);
        return $this->searchService->invoke($command)->getResult()->getTotal();
    }

    /**
     * Get the number of event records related to this agent
     *
     * @return int Number of records
     */
    public function getEventCount()
    {
        $id = $this->getUniqueId();
        $query = new \VuFindSearch\Query\Query(
            'author_id:"' . $id . '"'
        );
        // Disable highlighting for efficiency; not needed here:
        $params = new \VuFindSearch\ParamBag(['hl' => ['false']]);
        $command = new \VuFindSearch\Command\SearchCommand("SolrEvent", $query, 0, 0, $params);
        return $this->searchService->invoke($command)->getResult()->getTotal();
    }

    /**
     * Returns links to provider
     */
    public function getSameAs()
    {
        return $this->getEdmRecord()->getAttrVals("owl:sameAs", "foaf:Person");
    }
}
