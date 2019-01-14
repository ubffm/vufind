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
trait EdmReaderTrait
{
  /**
     * EDM record. Access only via getEdmRecord().
     *
     * @var EDM\EdmRecord
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
        $edm = $this->fields['fullrecord'];
        // check if we are dealing with EDMXML
        if (substr($edm, 0, 1) == '<') {
            $this->edmRecord = new EDM\EdmRecord($edm);
        } else {

        }
        if (!$this->edmRecord) {
            throw new EDM\EdmException('Cannot Process Edm Record');
        }
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
            $xml = $this->getEdmRecord()->toXML();
            if (!$xml) {
                return false;
            }

            return $xml;
        }

        // Try the parent method:
        return parent::getXML($format, $baseUrl, $recordLink);
    }

}
