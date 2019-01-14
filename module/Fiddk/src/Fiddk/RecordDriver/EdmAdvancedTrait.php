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
trait EdmAdvancedTrait
{

    public function isArchiveRecord() {
      $query = new \VuFindSearch\Query\Query(
              'id:"'.$this->getUniqueId().'"'.'+archive:true'
        );
      // Disable highlighting for efficiency; not needed here:
      $params = new \VuFindSearch\ParamBag(['hl' => ['false']]);
      return $this->searchService
              ->search($this->sourceIdentifier, $query, 0, 0, $params)
              ->getTotal();
    }

    public function getKVKLink() {
      $url = "";
      $base = "http://kvk.bibliothek.kit.edu/?kataloge=SWB&kataloge=BVB&kataloge=NRW&kataloge=HEBIS&kataloge=HEBIS_RETRO&kataloge=KOBV_SOLR&kataloge=GBV&kataloge=DDB&kataloge=STABI_BERLIN&kataloge=WORLDCAT&digitalOnly=0&embedFulltitle=0&newTab=1&";
      $exclude = in_array("Tanzfonds Erbe",$this->getInstitutions());

      if (!$exclude) {
        $isbn = $this->getCleanISBN();
        if ($isbn) {
          $url = $base . "SB=" . $isbn . "&";
        } elseif ($this->getTitle()) {
          $url = $base . "TI=" . $this->getTitle() . "&";
          if (isset($this->getPublicationDetails()[0])) {
            $url = $url . "PY=" . $this->getPublicationDetails()[0]->getDate() . "&";
          }
          if (isset($this->getPublishers()[0])) {
            $url = $url . "PU=" . $this->getPublishers()[0] . "&";
          }
        }
      }

      if ($url) {
        return $url . "autosubmit";
      } else {
         return NULL;
      }
    }

    public function getTitleIfExists($id,$source) {
      $query = new \VuFindSearch\Query\Query(
              'id:"'.$id.'"'
        );
      // Disable highlighting for efficiency; not needed here:
      $params = new \VuFindSearch\ParamBag(['hl' => ['false']]);
      $response = $this->searchService
              ->search($source, $query, 0, 1, $params);
      $record = current($response->getRecords());
      return $record->getTitle() .' ('.implode(', ', $record->getFormats()).')';
    }

}
