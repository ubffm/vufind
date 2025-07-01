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
 * @author  Demian Katz <demian.katz@villanova.edu>
 * @author  Ere Maijala <ere.maijala@helsinki.fi>
 * @author  Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
namespace Fiddk\RecordDriver;

use \Fiddk\Connection\Lobid;
use \Fiddk\Connection\Wikipedia;

/**
 * Model for EDM authority records in Solr.
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
class SolrAuthDefault extends SolrDefault implements
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
     * Wikipedia client
     *
     * @var Wikipedia
     */
    protected $wikipedia;

    protected $relatedResources;

    protected $relatedWorks;

    protected $relatedEvents;

    /**
     * Get the short (pre-subtitle) title of the record.
     *
     * @return string
     */
    public function getShortTitle()
    {
        // No difference between short and long titles for authority records:
        return $this->getTitle();
    }

    /**
     * Get the full title of the record.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->fields['heading'] ?? '';
    }

    /**
     * Get the see also references for the record.
     *
     * @return array
     */
    public function getSeeAlso()
    {
        return isset($this->fields['see_also'])
            && is_array($this->fields['see_also'])
            ? $this->fields['see_also'] : [];
    }

    /**
     * Get the use for references for the record.
     *
     * @return array
     */
    public function getUseFor()
    {
        return isset($this->fields['use_for'])
            && is_array($this->fields['use_for'])
            ? $this->fields['use_for'] : [];
    }

    /**
     * No support for citation of authority data
     */
    protected function getSupportedCitationFormats()
    {
        return [];
    }

    /**
     * Returns the authority type (Personal Name, Corporate Name or Event)
     */
    public function getRecordType()
    {
        switch ($this->fields['record_type']) {
            case 'Personal Name':
                return 'Person';
                break;
            case 'Corporate Name':
                return 'Corporation';
                break;
            default:
              return $this->fields['record_type'] ?? '';
        }
    }

    /**
     * Returns the type of the entity
     */
    public function getEntityType()
    {
        return $this->fields['entity_type'] ?? [];
    }

    /**
     * Get GND Type
     */
    public function getGndType()
    {
        return $this->fields['type'] ?? [];
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
        $this->wikipedia = new Wikipedia($this->client);
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
     * Get geographic area code of entity.
     */
    public function getGndGeographicAreaCode()
    {
        return $this->extraDetails['geographicAreaCode'] ?? [];
    }

    /**
     * Get GND subject category of entity.
     */
    public function getGndSubjectCategory()
    {
        return $this->extraDetails['gndSubjectCategory'] ?? [];
    }

    /**
     * Get homepage of person.
     */
    public function getGndHomepage()
    {
        return $this->extraDetails['homepage'] ?? [];
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
     * Returns the thumbnail url or []
     */
    public function getThumbnail($size = 'small')
    {
        return $this->fields['thumbnail'] ?? [];
    }

    /**
     * Returns further links (Personal Name, Corporate Name or Event)
     */
    public function getSource()
    {
        $links = [];
        if (isset($this->fields['links'])) {
            foreach ($this->fields['links'] as $link) {
                // TODO: Map for which kind of link
                $links[] = ["id" => $link, "name" => "Theaterlexikon der Schweiz"];
            }
        }
        return $links;
    }

    public function getDescription()
    {
        return $this->fields['description'] ?? [];
    }

    public function queryRecordId($id, $source)
    {
        $query = new \VuFindSearch\Query\Query(
            'id:"' . $id . '"'
        );
        // Disable highlighting for efficiency; not needed here:
        $params = new \VuFindSearch\ParamBag(['hl' => ['false']]);
        $command = new \VuFindSearch\Command\SearchCommand($source, $query, 0, 1, $params);
        $response = $this->searchService->invoke($command)->getResult();
        return $response;
    }

    // test if record exists
    public function checkExistence($id, $source)
    {
        $response = $this->queryRecordId($id, $source);
        return $response->getTotal();
    }

    /**
     * Get the number of work records related to this entity
     *
     *
     */
    public function getRelatedWorkCount($field)
    {
        $id = $this->getUniqueId();
        $query = new \VuFindSearch\Query\Query(
            $field . ':"' . $id . '"'
        );
        // Disable highlighting for efficiency; not needed here. And only get titles
        $params = new \VuFindSearch\ParamBag(['hl' => ['false'], 'fl' => 'id, title, format, date_span_sort']);
        $command = new \VuFindSearch\Command\SearchCommand("SolrWork", $query, 0, 5, $params);
        $collection = $this->searchService->invoke($command)->getResult();
        $this->relatedWorks = $collection->getRecords();
        return $collection->getTotal();
    }

    /**
     * Get related work records
     *
     *
     */
    public function getRelatedWorks()
    {
        return $this->relatedWorks;
    }

    /**
     * Get the number of resource records related to this entity
     *
     *
     */
    /* public function getRelatedResourceCount($field)
    {
        $id = $this->getUniqueId();
        $query = new \VuFindSearch\Query\Query(
            $field . ':"' . $id . '"'
        );
        // Disable highlighting for efficiency; not needed here. And only get titles
        $params = new \VuFindSearch\ParamBag(['hl' => ['false'], 'fl' => 'id, title, format, date_span_sort']);
        $command = new \VuFindSearch\Command\SearchCommand("Solr", $query, 0, 5, $params);
        $collection = $this->searchService->invoke($command)->getResult();
        $this->relatedResources = $collection->getRecords();
        return $collection->getTotal();
    } */

    /**
     * Get the number of resource records related to this entity
     *
     *
     */
    public function getRelatedResourceCount($fields)
    {
        $id = $this->getUniqueId();
        $queryStr = "";
        foreach ($fields as $field) {
            if (empty($queryStr)) {
                $queryStr .= $field . ':"' . $id . '"';
            }
            else {
                $queryStr .= " OR " . $field . ':"' . $id . '"';
            }
        }
        $query = new \VuFindSearch\Query\Query($queryStr);
        // Disable highlighting for efficiency; not needed here. And only get titles
        $params = new \VuFindSearch\ParamBag(['hl' => ['false'], 'fl' => 'id, title, format, date_span_sort']);
        $command = new \VuFindSearch\Command\SearchCommand("Solr", $query, 0, 5, $params);
        $collection = $this->searchService->invoke($command)->getResult();
        $this->relatedResources = $collection->getRecords();
        return $collection->getTotal();
    }

    /**
     * Get related resource records
     *
     *
     */
    public function getRelatedResources()
    {
        return $this->relatedResources;
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
        // Disable highlighting for efficiency; not needed here. And only get titles
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

    /**
     * Returns the source of the picture (ajax?)
     */
    public function getPicSource($thumbnail)
    {
        if (preg_match('/.+\/Special:FilePath\/(.+)\?.+/', $thumbnail, $fname)) :
            $picSource = $this->wikipedia->getJSON("&prop=imageinfo&iiprop=extmetadata&titles=File:" . $fname[1]);
            // $imageInfo = current($picSource)["imageinfo"]["0"]["extmetadata"];
            $firstPic = current($picSource);
            if (is_array($firstPic) 
                && isset($firstPic["imageinfo"][0]["extmetadata"])) {
                $imageInfo = $firstPic["imageinfo"][0]["extmetadata"];
            } else {
                $imageInfo = null; // oder Standardwert, oder Fehlerbehandlung
            }
            if (isset($imageInfo["Artist"]["value"]) && isset($imageInfo["LicenseShortName"]["value"])) :
                return [$imageInfo["Artist"]["value"],
                  "https://commons.wikimedia.org/wiki/File:" . $fname[1],
                  $imageInfo["LicenseShortName"]["value"]]; else:
                      return null;
                  endif; else:
                      return null;
                  endif;
    }
}
