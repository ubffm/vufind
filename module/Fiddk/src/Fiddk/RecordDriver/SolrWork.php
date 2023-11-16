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

    protected $relatedEvents;


    /**
     * Get icon for this entity type.
     *
     * @return string
     */
    public function getRecordIcon()
    {
        return 'icons/lightbulb.svg';
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getRecordType()
    {
        return "Work";
    }

    /**
     * Returns the type of the entity
     */
    public function getEntityType()
    {
        return $this->fields['entity_type'] ?? [];
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
     * @return array
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
     * Get GND date of production of entity.
     */
    public function getGndDateOfProduction()
    {
        return $this->extraDetails['dateOfProduction'] ?? [];
    }

    /**
     * Get GND subject category of entity.
     */
    public function getGndSubjectCategory()
    {
        return $this->extraDetails['gndSubjectCategory'] ?? [];
    }

    /**
     * Get geographic area code of entity.
     */
    public function getGndGeographicAreaCode()
    {
        return $this->extraDetails['geographicAreaCode'] ?? [];
    }

    /**
     * Get GND opus number of entity.
     */
    public function getGndOpusNum()
    {
        return $this->extraDetails['opusNumericDesignationOfMusicalWork'] ?? [];
    }

    /**
     * Get GND author of entity.
     */
    public function getGndAuthor()
    {
        return $this->extraDetails['author'] ?? [];
    }

    /**
     * Get GND librettist of entity.
     */
    public function getGndLibrettist()
    {
        return $this->extraDetails['librettist'] ?? [];
    }

    /**
     * Get GND composer of entity.
     */
    public function getGndComposer()
    {
        return $this->extraDetails['firstComposer'] ?? [];
    }

    /**
     * Get GND form of work or expression of entity.
     */
    public function getGndFormOfWork()
    {
        return $this->extraDetails['formOfWorkAndExpression'] ?? [];
    }

    /**
     * Get GND literary source of entity.
     */
    public function getGndLiterarySource()
    {
        return $this->extraDetails['literarySource'] ?? [];
    }

    /**
     * Get GND medium of performance of entity.
     */
    public function getGndMediumOfPerformance()
    {
        return $this->extraDetails['mediumOfPerformance'] ?? [];
    }

        /**
     * Get GND number of ensembles of entity.
     */
    public function getGndTotalNumberOfEnsembles()
    {
        return $this->extraDetails['totalNumberOfEnsembles'] ?? "";
    }

        /**
     * Get GND number of performers of entity.
     */
    public function getGndTotalNumberOfPerformers()
    {
        return $this->extraDetails['totalNumberOfPerformers'] ?? "";
    }


    /**
     * Get GND sameAs list
     */
    public function getGndSameAs()
    {
        return $this->extraDetails['sameAs'] ?? [];
    }

    /**
     * Get GND provider info
     */
    public function getGndDescribedBy()
    {
        return $this->extraDetails['describedBy'] ?? [];
    }

    /**
     * Returns links to provider
     */
    public function getSameAs()
    {
        return $this->getEdmRecord()->getAttrVals("owl:sameAs", "edm:ProvidedCHO");
    }

    public function getWorkRelatedEventCount()
    {
        return $this->getRelatedEventCount('work_id');
    }

     /**
     * Get the number of event records related to this entity
     *
     *
     */
    public function getRelatedEventCount($field)
    {
        $id = $this->getUniqueId();
        $query = new \VuFindSearch\Query\Query(
            $field . ':"' . $id . '"'
        );
        // Disable highlighting for efficiency; not needed here. Get titles only.
        $params = new \VuFindSearch\ParamBag(['hl' => ['false'], 'fl' => 'id, heading, date']);
        $command = new \VuFindSearch\Command\SearchCommand("SolrEvent", $query, 0, 5, $params);
        $collection = $this->searchService->invoke($command)->getResult();
        $this->relatedEvents = $collection->getRecords();
        return $collection->getTotal();
    }

    /**
     * Get related event records
     *
     *
     */
    public function getRelatedEvents()
    {
        return $this->relatedEvents;
    }

}
