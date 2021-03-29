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
namespace Fiddk\RecordDriver;
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

    public function getInstitution() {
      return isset($this->fields['institution']) ? $this->fields['institution'] : '';
    }

    public function getIntermediate() {
      return isset($this->fields['intermediate']) ? $this->fields['intermediate'] : '';
    }

    public function getAlternativeTitles() {
      return isset($this->fields['title_alt']) ? $this->fields['title_alt'] : [];
    }

    public function getGenres() {
      return isset($this->fields['genre']) ? $this->fields['genre'] : [];
    }

      /**
     * Get the titles of parent titles.
     *
     * @return array
     */
    public function getContainerTitle()
    {
        return isset($this->fields['container_title']) ? $this->fields['container_title'] : [];
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

    /**
     * Get an array of detail lines combining information from
     * getDates() and getPlaces(). This is the version that has nothing
     * to do with publication but contains vague dates and places.
     *
     * @return array
     */
     public function getPlaceDateDetails()
     {
       $places = $this->getPlaces();
       $dates = $this->getHumanReadableDates();
       $i = 0;
       $retval = [];
       while (isset($places[$i]) || isset($dates[$i])) {
        // Build objects to represent each set of data; these will
        // transform seamlessly into strings in the view layer.
        $retval[] = new \VuFind\RecordDriver\Response\PublicationDetails(
            $places[$i] ?? '',
            '',
            $dates[$i] ?? ''
        );
        $i++;
       }
      return $retval;
    }

    /* All those fields that are not in the index, but need to be displayed */
    /* Literals*/
    public function getExtent() {
      return $this->getEdmRecord()->getLiteralVals("dcterms:extent", "edm:ProvidedCHO");
    }

    public function getCallNumber() {
      return $this->getEdmRecord()->getLiteralVals("bf:shelfMark", "edm:ProvidedCHO");
    }

    public function getVolume() {
      return $this->getEdmRecord()->getLiteralVals("bibo:volume", "edm:ProvidedCHO");
    }

    public function getProvenance() {
      return $this->getEdmRecord()->getLiteralVals("dcterms:provenance", "edm:ProvidedCHO");
    }

    public function getTOC() {
      $tocs = $this->getEdmRecord()->getLiteralVals("dcterms:tableOfContents", "edm:ProvidedCHO");
      if ($tocs) {
        $res = [];
        foreach ($tocs as $toc) {
          //TODO: what if table of contents is a link?
          $res = array_merge($res,array_filter(explode('--', $toc), 'trim'));
        }
        return $res;
      } else
        return [];
    }

    public function getAccessRestrictions() {
      return $this->getEdmRecord()->getLiteralVals("dc:rights", "ore:Aggregation");
    }

    /* Attribute Vals */
    public function getHumanReadablePublicationDates() {
      return $this->getEdmRecord()->getAttrVals("dcterms:issued", "edm:ProvidedCHO");
    }

    /* Literal or Attribute Vals */
    public function getHumanReadableDates() {
      return $this->getEdmRecord()->getPropValues("dcterms:temporal", "edm:ProvidedCHO", "");
    }

    public function getPlacesOfPublication() {
      return $this->getEdmRecord()->getPropValues("rdau:P60163", "edm:ProvidedCHO", "skos:prefLabel");
    }

    public function getPlacesOfManufacture() {
      return $this->getEdmRecord()->getPropValues("rdau:P60162", "edm:ProvidedCHO", "skos:prefLabel");
    }

    public function getPlaces() {
      return $this->getEdmRecord()->getPropValues("dcterms:spatial", "edm:ProvidedCHO", "skos:prefLabel");
    }

    // TODO: remove
    public function getPerformancePlaces() {
      return $this->getEdmRecord()->getPropValues("eclap:performancePlace", "edm:ProvidedCHO", "skos:prefLabel");
    }

    /* Prepare attribute val with link */
    public function getLicenseLink() {
      $licenseLinks = [];
      $inst = $this->getInstitution();
      if ($inst == 'transcript Verlag' or $inst == 'Alexander Street Press' or $inst == 'Adam Matthew Digital') {
        $licenseLinks = $this->getEdmRecord()->getLinkedPropValues("edm:isShownAt","ore:Aggregation","dc:description");
      }
      return $licenseLinks;
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
        if ($inter == "BASE") {
            $instkey = explode("_",$this->getEdmRecord()->getAttrVals("edm:dataProvider", "ore:Aggregation")[0])[1];
            $instlink = "https://www.base-search.net/Search/Results?q=dccoll:" . $instkey;
            $instid = "BASE";
          }
          else {
            $instkey = preg_replace( "/\r|\n|\s|,|\/|\(|\)/", "", $inst);
            $info = explode(',',$dprovConf[$instkey]);
            $instlink = $info[0];
            $instid = $info[1];
          }
          $interkey = preg_replace( "/\r|\n|\s|,|\/|\(|\)/", "", $inter);
          $info = explode(',',$dprovConf[$interkey]);
          $interlink = $info[0];
          $res = [$type => [$inter,$interlink,$instid,$inst,$instlink]];
      } else {
        $type = "inst";
        $instkey = preg_replace( "/\r|\n|\s|,|\/|\(|\)/", "", $inst);
        $info = explode(',',$dprovConf[$instkey]);
        $res = [$type => [$inst,$info[0],$info[1]]];
      }
      return $res;
    }

    public function getDigitalCopies() {
      $links = [];
      $inst = $this->getInstitution();
      // prevent duplicates
      if ($inst != 'transcript Verlag' and $inst != 'Alexander Street Press' and $inst != 'Adam Matthew Digital') {
        $links = $this->getEdmRecord()->getLinkedPropValues("edm:isShownAt","ore:Aggregation","dc:description");
      }
      return $links + $this->getEdmRecord()->getLinkedPropValues("edm:isShownBy","ore:Aggregation","dc:description") +
             $this->getEdmRecord()->getLinkedPropValues("edm:hasView","ore:Aggregation","dc:description");
    }

    public function getCatalogueLink() {
      return $this->getEdmRecord()->getLinkedPropValues("dm2e:hasAnnotatableVersionAt","ore:Aggregation","dc:description");
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
        return isset($this->fields['author_id']) ?
            $this->fields['author_id'] : [];
    }

    /**
     * Get related events
     *
     * @return array
     */
    public function getEvents() {
      $eventTitles = isset($this->fields['event']) ?
          $this->fields['event'] : [];
      $eventIds = isset($this->fields['event_id']) ?
          $this->fields['event_id'] : [];
      if (isset($eventTitles[0])) {
        return array_combine($eventIds,$eventTitles);
      } else {
        return [];
      }
    }

    /**
     * Get related works
     *
     * @return array
     */
    public function getWorks() {
      $workTitles = isset($this->fields['work']) ?
          $this->fields['work'] : [];
      $workIds = isset($this->fields['work_id']) ?
          $this->fields['work_id'] : [];
      if (isset($workTitles[0])) {
        return array_combine($workIds,$workTitles);
      } else {
        return [];
      }
    }

  /**
   * Get all record links related to the current record.
 *
 * @return null|array
 */
    public function getAllRecordLinks() {
      $retVal = [];
      if (isset($this->fields['related_to'])) {
        $ids = $this->fields['related_to'];
        array_walk($ids, function (&$id) use (&$retVal) {
          $title = $this->getTitleIfExists($id,$this->sourceIdentifier);
          if ($title) {
            $retVal[$id] = $title;
          }
        });
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
        $headings = isset($this->fields['topic']) ? $this->fields['topic'] : [];
        $contextIds = isset($this->fields['topic_id']) ? $this->fields['topic_id'] : [];
        $contextRoles = isset($this->fields['topic_role']) ? $this->fields['topic_role'] : [];

        if ($extended) {

          foreach ($headings as $i => $heading) {
            $retVal[] = [
              'heading' => $heading,
              'type' => isset($contextRoles[$i]) ? $contextRoles[$i] : '',
              'source' => isset($contextIds[$i]) ? $contextRoles[$i] : ''
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
      return isset($this->fields['thumbnail']) ?
          $this->fields['thumbnail'] : [];
    }

    /**
     * Return year easily for purposes like in playbill theme portal
     *
     * @return int
     */
    public function getYear()
    {
      return isset($this->fields['date_span_sort']) ?
          substr($this->fields['date_span_sort'],0,4) : 0;
    }

}
