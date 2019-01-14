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

    public function getExtent() {
      return $this->getEdmRecord()->getPropValues("dcterms:extent");
    }

    public function getCallNumber() {
      return $this->getEdmRecord()->getPropValues("dm2e:callNumber");
    }

    public function getProvenance() {
      return $this->getEdmRecord()->getPropValues("dcterms:provenance");
    }

    public function getTOC() {
      return $this->getEdmRecord()->getPropValues("dcterms:tableOfContents");
    }

    public function getHumanReadablePublicationDates() {
        return $this->getEdmRecord()->getPropValues("dcterms:issued");
    }

    public function getHumanReadableDates() {
        return $this->getEdmRecord()->getPropValues("dc:date");
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
      $inst = $this->getInstitutions()[0];
      if ($inst == 'transcript Verlag' or $inst == 'Alexander Street Press') {
        $licenseLinks = $this->getEdmRecord()->getPropValues("edm:isShownAt");
      }
      return $licenseLinks;
    }

    public function getInstitutionsLinked() {
      return $this->getEdmRecord()->getLinkedPropValues("edm:dataProvider","skos:prefLabel", "foaf:homepage");
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
    // deduplicate
    $dedup = function (&$array1, &$array2) {
        if (!empty($array1) && !empty($array2)) {
            $keys = array_keys($array1);
            foreach ($keys as $author) {
                if (isset($array2[$author])) {
                    $array1[$author] = array_merge(
                        $array1[$author],
                        $array2[$author]
                    );
                    unset($array2[$author]);
                }
            }
        }
    };
    $dedup($authors['primary'], $authors['corporate']);
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

}
