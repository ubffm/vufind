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
trait EdmReaderTrait
{
    /**
    * Edm reader. Access only via getEdmReader() as this is initialized lazily.
    */
   protected $edmRecord = null;

    protected $loader = null;

    /**
     * Get access to the raw File_EDM object.
     *
     * @return EDM\EdmRecord
     */
    public function getEdmRecord()
    {
        if (null === $this->edmRecord) {
            $edm = $this->fields['fullrecord'] ?? '';
            // check if we are dealing with EDMXML
            if (substr($edm, 0, 1) == '<') {
                $this->edmRecord = new EdmRecord($edm);
                return $this->edmRecord;
            } else {
                throw new \Exception("Not an EDM record: " . implode(';',$this->fields));
            }
        }
    }

        /**
     * Get access to the raw File_EDM object.
     *
     * @return EDM\EdmRecord
     */
    public function getEdmReader()
    {
        if (null === $this->edmRecord) {
            $this->edmRecord = $this->getEdmRecord();
        }

        return $this->edmRecord;
    }

    /**
     * Return an XML representation of the record using the specified format.
     * Return false if the format is unsupported.
     *
     * @param string     $format     Name of format to use (corresponds with OAI-PMH
     * metadataPrefix parameter).
     * @param string     $baseUrl    Base URL of host containing VuFind (optional;
     * may be used to inject record URLs into XML when appropriate).
     * @param RecordLink $recordLink Record link helper (optional; may be used to
     * inject record URLs into XML when appropriate).
     *
     * @return mixed         XML, or false if format unsupported.
     */
    public function getXML($format, $baseUrl = null, $recordLink = null)
    {
        // Special case for EDM:
        if ($format == 'edm') {
            $xml = $this->edmRecord ?? $this->getEdmRecord();

            if (!$xml) {
                return false;
            }

            return $xml;
        }

        // Try the parent method:
        return parent::getXML($format, $baseUrl, $recordLink);
    }

    /* this should be a helper instead! */
    public function translateDate($date, $lang)
    {
        // FIXME
        if (strpos($date, "T00:00:00Z_")) {
            $dateParts = explode("_", $date);
        } elseif (strpos($date, "T00:00:00Z TO ")) {
            $dateParts = explode(" TO ", str_replace("]", "", str_replace("[", "", $date)));
        } else {
            return $date;
        }
        $start = isset($dateParts[0]) ? explode("T",$dateParts[0])[0] : "";
        $end = isset($dateParts[1]) ? explode("T",$dateParts[1])[0] : "";
        if ($start == $end) {
            // exact date
            return $this->formatToDate($start, $lang);
        // catching bc dates as well
        } elseif (substr($start, 0, 5) == substr($end, 0, 5)) {
            if (preg_match("/01-01$/",$start) && preg_match("/12-31$/",$end)) {
                // whole year
                // BC
                if (strpos($start,"-") === 0) {
                  return substr($start, 0, 5);
                }
                else {
                  return substr($start, 0, 4);
                }
            } else {
                // two exact dates in same year
                return $this->formatToDate($start, $lang) . " - " . $this->formatToDate($end, $lang);
            }
        } elseif (preg_match("/01-01$/",$start) && preg_match("/12-31$/",$end)) {
            if (intval(substr($start, 0, 4)) + 1 == intval(substr($end, 0, 4))) {
                // season
                return substr($start, 0, 4) . "/" . substr($end, 0, 4);
            } else {
                // whole year span
                return substr($start, 0, 4) . "-" . substr($end, 0, 4);
            }
        } elseif (substr($start, 5, 5) == "01-01" && $end == "*") {
            // since
            return substr($start, 0, 4) . "-";
        } elseif ($start == "*" && substr($end, 5, 5) == "12-31") {
            // until
            return "-" . substr($end, 0, 4);
        } else {
            // two exact dates
            return $this->formatToDate($start, $lang) . " - " . $this->formatToDate($end, $lang);
        }
    }

    public function formatToDate($date, $lang)
    {
        if ($lang == 'en') {
            return $date;
        } else {
          // BC
          if (strpos($date,"-") === 0) {
            return substr($date, 9, 2) . "." . substr($date, 6, 2) . "." . substr($date, 1, 5) . " v. Chr.";
          } else {
            return substr($date, 8, 2) . "." . substr($date, 5, 2) . "." . substr($date, 0, 4);
          }
        }
    }
}
