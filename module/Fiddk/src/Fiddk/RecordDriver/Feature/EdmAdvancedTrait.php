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
trait EdmAdvancedTrait
{
    public function isArchiveRecord()
    {
        $query = new \VuFindSearch\Query\Query(
            'id:"' . $this->getUniqueId() . '"' . '+(archive:true OR format:Werk)'
        );
        // Disable highlighting for efficiency; not needed here:
        $params = new \VuFindSearch\ParamBag(['hl' => ['false']]);
        $command = new \VuFindSearch\Command\SearchCommand($this->sourceIdentifier, $query, 0, 0, $params);
        return $this->searchService
            ->invoke($command)->getResult()->getTotal();
    }

    public function askArchive()
    {
        $callNumber = "";
        if ($this->isArchiveRecord() && (strpos($this->getUniqueId(), "DTK") === 0 || strpos($this->getUniqueId(), "DTMA") === 0 || strpos($this->getUniqueId(), "MCB") === 0)) {
            if ($this->getCallNumber()) {
                $callNumber = $this->getCallNumber()[0];
            } else {
                $callNumber = substr($this->getUniqueId(),strpos($this->getUniqueId(), "_") + 1);
            }
        }
        return $callNumber;
    }

    public function getKVKLink()
    {
        $url = "";
        $base = "http://kvk.bibliothek.kit.edu/?kataloge=SWB&kataloge=BVB&kataloge=NRW&kataloge=HEBIS&kataloge=HEBIS_RETRO&kataloge=KOBV_SOLR&kataloge=GBV&kataloge=DDB&kataloge=STABI_BERLIN&kataloge=WORLDCAT&digitalOnly=0&embedFulltitle=0&newTab=1&";
        if (isset($this->getInstitutions()[0])) {
            $inst = $this->getInstitutions()[0]; 
        } else {
            $inst = "";
        }
        $exclude = in_array($inst, ["Tanzfonds Erbe", "Thüringer Universitäts- und Landesbibliothek Jena"]) or $this->isArchiveRecord();

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
            return htmlspecialchars($url) . "autosubmit";
        } else {
            return null;
        }
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

    // get title and format of related record
    public function getTitleIfExists($id, $source)
    {
        $response = $this->queryRecordId($id, $source);
        $record = current($response->getRecords());
        if ($record and $record->getFormats()) {
            return $record->getTitle() . ' (' . implode(', ', $record->getFormats()) . ')';
        } elseif ($record) {
            return $record->getTitle();
        }
    }

    // get date of related event
    public function getEventDate($id)
    {
        $response = $this->queryRecordId($id, "SolrEvent");
        $record = current($response->getRecords());
        if ($record and $record->getEventDate()) {
            return $record->getEventDate();
        }
    }
}
