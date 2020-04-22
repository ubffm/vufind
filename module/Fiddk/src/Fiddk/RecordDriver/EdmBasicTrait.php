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

    public function getExtent() {
      return $this->getEdmRecord()->getPropValues("dcterms:extent");
    }

    public function getCallNumber() {
      return $this->getEdmRecord()->getPropValues("dm2e:callNumber");
    }

    public function getAlternativeTitles() {
      return $this->getEdmRecord()->getPropValues("dcterms:alternative");
    }

    public function getVolume() {
      return $this->getEdmRecord()->getPropValues("bibo:volume");
    }

    public function getProvenance() {
      return $this->getEdmRecord()->getPropValues("dcterms:provenance");
    }

    public function getTOC() {
      return $this->getEdmRecord()->getPropValues("dcterms:tableOfContents");
    }

    public function getHumanReadablePublicationDates() {
        return $this->getEdmRecord()->getAttrVal("dcterms:issued");
    }

    public function getHumanReadableDates() {
        //return $this->getEdmRecord()->getAttrVal("dcterms:temporal");
        return $this->getEdmRecord()->getPropValues("dcterms:temporal");
    }

    public function getPlacesOfPublication() {
        return $this->getEdmRecord()->getPropValues("dm2e:publishedAt");
    }

    public function getPlaces() {
        return $this->getEdmRecord()->getPropValues("dcterms:spatial");
    }

    public function getPerformancePlaces() {
        return $this->getEdmRecord()->getPropValues("eclap:performancePlace");
    }

    /**
     * Get the titles of parent titles.
     *
     * @return array
     */
    public function getContainerTitle()
    {
        return isset($this->fields['container_title'])
            ? $this->fields['container_title'] : [];
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
 * getDates() and getPlaces(). THis is the version that has nothing
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


    public function getLicenseLink() {
      $licenseLinks = [];
      $inst = $this->getInstitution();
      if ($inst == 'transcript Verlag' or $inst == 'Alexander Street Press') {
        $licenseLinks = $this->getEdmRecord()->getLinkedPropValues("edm:isShownAt","dc:description","");
      }
      return $licenseLinks;
    }

    public function getInstitutionLinked() {
      $inst = $this->getInstitution();
      $dprovConf = $this->mainConfig->DataProvider;
      $instkey = preg_replace( "/\r|\n|\s|,|\//", "", $inst );
      return [$inst => explode(',',$dprovConf[$instkey])];
    }

    public function getDigitalCopies() {
      return $this->getEdmRecord()->getLinkedPropValues("edm:hasView","dc:description","");
    }

    public function getCatalogueLink() {
      return $this->getEdmRecord()->getLinkedPropValues("dm2e:hasAnnotatableVersionAt","dc:description","");
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

        if ($extended) {
          //$subjects = $this->getEdmRecord()->getLinkedPropValues("dc:subject","skos:prefLabel","");
          /*foreach ($subjects as $link => $subject) {
            $linkparts = explode('/',$link);
            if (isset($linkparts[3]) && $linkparts[3] == 'agent') {
              $retVal[] = [
                'heading' => $subject,
                'type' => 'agent',
                'source' => $linkparts[4] . '_' . $linkparts[5]
              ];
            }
          }*/
          foreach ($headings as $heading) {
            $retVal[] = [
              'heading' => $heading,
              'type' => 'subject',
              'source' => ''
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

}
