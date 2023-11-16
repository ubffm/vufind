<?php
/**
 * Functions for reading EDM records.
 *
 * PHP version 7
 *
 * Copyright (C) Frankfurt University Library
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
 * @package  RecordDrivers
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 */
namespace Fiddk\RecordDriver\Feature;

/**
 * Additional functionality for Edm Solr records.
 *
 * @category VuFind
 * @package  RecordDrivers
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
trait EdmBasicTrait
{

    public function getRecordType() {
        return 'Record';
    }

    public function getEntityType() {
        return 'Record';
    }

    /**
     * Get icon for this entity type.
     *
     * @return string
     */
    public function getRecordIcon()
    {
        return 'icons/boxes.svg';
    }

    public function getAlternativeTitles()
    {
        return $this->fields['title_alt'] ?? [];
    }

    public function getShortTitle()
    {
        return parent::getTitle();
    }

    public function getGenres()
    {
        return $this->fields['genre'] ?? [];
    }

    /** Place related fields */

    // convenience method for API, carousels and result lists
    public function getGeographicsCon()
    {
        return $this->fields['geographic'] ?? [];
    }

    // places and their type
    public function getGeographicsType()
    {
        $retVal = [];
        $undefined = $this->getPlaces("dcterms:spatial");
        $publication = $this->getPlaces("rdau:P60163");
        $manufacture = $this->getPlaces("rdau:P60162");
        $event = $this->getPlaces("edm:happenedAt");
        $current = $this->getPlaces("edm:currentLocation");
        if (!empty($undefined)) {
            $retVal['undefinedPlace'] = $undefined;
        }
        if (!empty($publication)) {
            $retVal['edm::rdau:P60163'] = $publication;
        }
        if (!empty($manufacture)) {
            $retVal['edm::rdau:P60162'] = $manufacture;
        }
        if (!empty($event)) {
            $retVal['edm::edm:happenedAt'] = $event;
        }
        if (!empty($current)) {
            $retVal['edm::edm:currentLocation'] = $current;
        }
        return $retVal ?? [];
    }

    public function getPlaces($type)
    {   
        return $this->getEdmReader()->getPropValues($type, "edm:ProvidedCHO", "skos:prefLabel");
    }

    /** Date related fields */

    // convenience method for API, carousels and result lists
    public function getDatesCon()
    {
        return $this->fields['date_span'] ?? [];
    }

    // dates and their type
    public function getDatesType()
    {
        $retVal = [];
        $undefined = $this->getPlaces("dcterms:temporal");
        $publication = $this->getPlaces("dcterms:issued");
        $creation = $this->getPlaces("dcterms:created");
        $event = $this->getPlaces("edm:occuredAt");
        if (!empty($undefined)) {
            $retVal['undefinedDate'] = $undefined;
        }
        if (!empty($publication)) {
            $retVal['edm::dcterms:issued'] = $publication;
        }
        if (!empty($creation)) {
            $retVal['edm::dcterms:created'] = $creation;
        }
        if (!empty($event)) {
            $retVal['edm::edm:occuredAt'] = $event;
        }
        return $retVal ?? [];
    }

    public function getDates($type)
    {   
        return $this->getEdmReader()->getPropValues($type, "edm:ProvidedCHO", "skos:prefLabel");
    }

    public function getFormats(): array
    {
        return isset($this->fields['format']) ? array_unique($this->fields['format']) : [];
    }

    /**
     * Get the titles of parent titles.
     *
     * @return array
     */
    public function getContainerTitle()
    {
        return $this->fields['container_title'] ?? [];
    }

    /**
     * Get the container record ids.
     *
     * @return array Container record id (empty string if none)
     */
    public function getContainerRecordID()
    {
        return $this->containerLinking
            && !empty($this->fields['hierarchy_parent_id'])
            ? $this->fields['hierarchy_parent_id'] : [];
    }

    /* All those fields that are not in the index, but need to be displayed */
    /* Literals*/
    public function getExtent()
    {
        return $this->getEdmReader()->getLiteralVals("dcterms:extent", "edm:ProvidedCHO");
    }

    public function getCallNumber()
    {
        return $this->getEdmReader()->getLiteralVals("bf:shelfMark", "edm:ProvidedCHO");
    }

    public function getVolume()
    {
        $vol = $this->getEdmReader()->getLiteralVals("bf:partNumber", "edm:ProvidedCHO");
        if ($vol) {
            return $vol;
        } else {
            return $this->getEdmReader()->getLiteralVals("bibo:volume", "edm:ProvidedCHO");
        }
    }

    public function getProvenance()
    {
        return $this->getEdmReader()->getLiteralVals("dcterms:provenance", "edm:ProvidedCHO");
    }

    public function getTOC()
    {
        $tocs = $this->getEdmReader()->getLiteralVals("dcterms:tableOfContents", "edm:ProvidedCHO");
        if ($tocs) {
            $res = [];
            foreach ($tocs as $toc) {
                //TODO: what if table of contents is a link?
                $res = array_merge($res, array_filter(explode('--', $toc), 'trim'));
            }
            return $res;
        } else {
            return [];
        }
    }

    public function getAccessRestrictions()
    {
        $rights = [];
        if ($this->getEntityType() == "Record") {
            $rights = $this->getEdmReader()->getLiteralVals("dc:rights", "ore:Aggregation");
        }
        return empty($rights) ? $this->getEdmReader()->getLiteralVals("dc:rights", "edm:ProvidedCHO") : $rights;
    }

    /* Prepare attribute val with link */
    public function getLicenseLink()
    {
        $licenseLinks = [];
        if (isset($this->getInstitutions()[0])) {
            $inst = $this->getInstitutions()[0]; 
        } else {
            $inst = "";
        }
        if (isset($this->getInstitutions()[0])) {
            $inst = $this->getInstitutions()[0];
            if ($inst == 'transcript Verlag' or $inst == 'Alexander Street Press' or $inst == 'Adam Matthew Digital') {
                $licenseLinks = [$inst => $this->getEdmReader()->getLinkedPropValues("edm:isShownAt", "ore:Aggregation", "dc:description")];
            }
            return $licenseLinks;
        } else {
            return [];
            $inst = $this->getInstitutions()[0]; 
        if ($inst == 'transcript Verlag' or $inst == 'Alexander Street Press' or $inst == 'Adam Matthew Digital') {
            $licenseLinks = [$inst => $this->getEdmReader()->getLinkedPropValues("edm:isShownAt", "ore:Aggregation", "dc:description")];
        }
    }
    }

    public function getDigitalCopies()
    {
        $links = [];
        $inst = $this->getInstitutions()[0];
        // prevent duplicates
        if ($inst != 'transcript Verlag' and $inst != 'Alexander Street Press' and $inst != 'Adam Matthew Digital') {
            $links = $this->getEdmReader()->getLinkedPropValues("edm:isShownAt", "ore:Aggregation", "dc:description");
        }
        return $links + $this->getEdmReader()->getLinkedPropValues("edm:isShownBy", "ore:Aggregation", "dc:description") +
        $this->getEdmReader()->getLinkedPropValues("edm:hasView", "ore:Aggregation", "dc:description");
    }

    public function getCatalogueLink()
    {
        return $this->getEdmReader()->getLinkedPropValues("edm:isShownAt", "ore:Aggregation", "dc:description");
    }

    /**
     * Deduplicate author information into associative array with main/corporate/
     * secondary keys.
     *
     * @param array $dataFields An array of extra data fields to retrieve (see
     * getAuthorDataFields)
     *
     * @return array
     */
    public function getDeduplicatedAuthors($dataFields = ['role','id'])
    {
        $authors = [];
        foreach (['primary', 'corporate'] as $type) {
            $authors[$type] = $this->getAuthorDataFields($type, $dataFields);
        }
        $dedup_data = function (&$array) {
            foreach ($array as $author => $data) {
                foreach ($data as $field => $values) {
                    if (is_array($values)) {
                        $array[$author][$field] = array_unique($values);
                    }
                }
            }
        };
        $dedup_data($authors['primary']);
        $dedup_data($authors['corporate']);
        return $authors;
    }

    /**
     * Get an array of all main authors ids
     *
     * @return array
     */
    public function getPrimaryAuthorsIds()
    {
        return $this->fields['author_id'] ?? [];
    }

    /**
     * Get related events
     *
     * @return array
     */
    public function getEvents()
    {
        $eventTitles = $this->fields['event'] ?? [];
        $eventIds = $this->fields['event_id'] ?? [];
        if (isset($eventTitles[0])) {
            return array_combine($eventIds, $eventTitles);
        } else {
            return [];
        }
    }

    /**
     * Get related works
     *
     * @return array
     */
    public function getWorks()
    {
        $workTitles = $this->fields['work'] ?? [];
        $workIds = $this->fields['work_id'] ?? [];
        if (isset($workTitles[0])) {
            return array_combine($workIds, $workTitles);
        } else {
            return [];
        }
    }

    /**
     * Get all record links related to the current record.
     *
     * @return null|array
     */
    public function getAllRecordLinks()
    {
        $retVal = [];
        if (isset($this->fields['related_to'])) {
            $ids = $this->fields['related_to'];
            array_walk(
                $ids, function (&$id) use (&$retVal) {
                    $title = $this->getTitleIfExists($id, $this->sourceIdentifier);
                    if ($title) {
                        $retVal[$id] = $title;
                    }
                }
            );
        }
        return $retVal;
    }

    /**
     * Get all subject headings associated with this record.  Each heading is
     * returned as an array of chunks, increasing from least specific to most
     * specific.
     * Ignore geographic, era and genre. We don't use them as subjects.
     *
     * @param bool $extended Whether to return a keyed array with the following
     * keys:
     * - heading: the actual subject heading
     * - type: heading type (Agent, Place, Concept, Timespan)
     * - source: source vocabulary -> link (gnd)
     *
     * @return array
     */
    public function getAllSubjectHeadings($extended = true)
    {
        $retVal = [];
        $headings = $this->fields['topic'] ?? [];
        $contextIds = $this->fields['topic_id'] ?? [];
        $contextRoles = $this->fields['topic_role'] ?? [];

        if ($extended) {
            foreach ($headings as $i => $heading) {
                $retVal[] = [
                'heading' => $heading,
                'type' => $contextRoles[$i] ?? '',
                'source' => $contextIds[$i] ?? ''
                ];
            }
        }

        return $retVal;

        /* $callback = function ($i) use ($extended) {
            return $extended
                ? ['heading' => [$i], 'type' => '', 'source' => '']
                : [$i];
        };
        return array_map($callback, array_unique($headings)); */
    }

    /**
     * Return a thumbnail if it exists.
     * If there is none, don't try to generate a thumbnail.
     *
     * @param string $size Size of thumbnail (small, medium or large -- small is
     * default).
     *
     * @return array
     */
    public function getThumbnail($size = 'small')
    {
        return $this->fields['thumbnail'] ?? [];
    }

    /**
     * Return year easily for purposes like in playbill theme portal
     *
     * @return int
     */
    public function getYear()
    {
        return isset($this->fields['date_span_sort']) ?
          substr($this->fields['date_span_sort'], 0, 4) : 0;
    }
}
