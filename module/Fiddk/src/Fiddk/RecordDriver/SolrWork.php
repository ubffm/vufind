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
 * @author  Demian Katz <demian.katz@villanova.edu>
 * @author  Ere Maijala <ere.maijala@helsinki.fi>
 * @author  Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
namespace Fiddk\RecordDriver;

use \Fiddk\Connection\Lobid;

/**
 * Model for EDM work authority records in Solr.
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
class SolrWork extends SolrEdm implements
\VuFindHttp\HttpServiceAwareInterface
{
    use \VuFindHttp\HttpServiceAwareTrait;

    /**
     * HTTP client
     *
     * @var \Laminas\Http\Client
     */
    protected $client;

    /**
     * Lobid client
     *
     * @var Lobid
     */
    protected $lobid;


    /**
     * Get icon for this entity type.
     *
     * @return string
     */
    public function getRecordIcon()
    {
        return 'fa-star';
    }

    /**
     * Get type
     *
     * @return array
     */
    public function getRecordType()
    {
        return "Work";
    }

        /**
     * is this a GND record?
     */
    public function isGndRecord()
    {
        return str_starts_with($this->fields['id'],'gnd_') ?? false;
    }

    /**
     * Get GND record, but only in details pages
     */
    public function getGndRecord()
    {
        $gnd = substr($this->fields['id'], 4);
        $this->client = $this->httpService->createClient();
        $this->lobid = new Lobid($this->client);
        $this->extraDetails = $this->lobid->get($gnd);
        return $this->extraDetails;
    }

    /**
     * Get GND variant names
     */
    public function getGndVariants()
    {
        return $this->extraDetails['variantName'] ?? [];
    }

    /**
     * Get GND Number
     */
    public function getGndIdentifier()
    {
        return $this->extraDetails['gndIdentifier'] ?? [];
    }

    /**
     * Get biographical or historical information of Gnd entity.
     *
     * @return string
     */
    public function getGndBio()
    {
        return $this->extraDetails['biographicalOrHistoricalInformation'] ?? [];
    }

    /**
     * Get desciption of Gnd entity.
     *
     * @return string
     */
    public function getGndDescription()
    {
        return $this->extraDetails['description'] ?? [];
    }

    /**
     * Get GND date of publication of entity.
     */
    public function getGndDateOfPublication()
    {
        return $this->extraDetails['dateOfPublication'] ?? [];
    }

    /**
     * Get GND subject category of entity.
     */
    public function getGndSubjectCategory()
    {
        return $this->extraDetails['gndSubjectCategory'] ?? [];
    }

    /**
     * Get GND sameAs list
     */
    public function getGndSameAs()
    {
        return $this->extraDetails['sameAs'] ?? [];
    }

    /**
     * Returns links to provider
     */
    public function getSameAs()
    {
        return $this->getEdmRecord()->getAttrVals("owl:sameAs", "edm:ProvidedCHO");
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
        $command = new \VuFindSearch\Command\SearchCommand("SolrEvent", $query, 0, 0, $params);
        return $this->searchService->invoke($command)->getResult()->getTotal();
    }
}
