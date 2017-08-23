<?php
/**
 * Model for EDM records in Solr.
 *
 * PHP version 5
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
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @author   Julia Beck
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
namespace fiddk\RecordDriver;

    /**
     * Model for Solr authority records.
     *
     * @category VuFind
     * @package  RecordDrivers
     * @author   Demian Katz <demian.katz@villanova.edu>
     * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
     * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
     */
    class SolrAuth extends \fiddk\RecordDriver\SolrEdm
    {

        protected $entityLink = "http://hub.culturegraph.org/entityfacts/";

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
            return isset($this->fields['heading']) ? $this->fields['heading'] : '';
        }

        /**
         * Get the full title of the record.
         *
         * @return string
         */
        public function getFullRecord()
        {
            return isset($this->fields['fullrecord']) ? $this->fields['fullrecord'] : '';
        }

        public function loadEntityFacts($id)
        {
           $id = explode('_', $id)[1];
           $json = file_get_contents($this->entityLink . $id);
           return json_decode($json);
        }

        public function getAgentFacts($id,$full) {
          if (explode('_', $id)[0] == 'gnd') {
            return $this->getEntityFacts($id,$full);
          } else {
           // $id from provider with info
           // TODO: load info from index
          }
        }

        public function getEntityFacts($id,$full) {

            $agent = new Agent();
            $obj = $this->loadEntityFacts($id);
            if ($obj) {
               $agent->name = \Normalizer::normalize($obj->preferredName,\Normalizer::NFC);
               $agent->depiction = $obj->depiction->{"thumbnail"}->{"@id"};
               if ($obj->{"@type"} == 'person') {
                   $agent->birthDate = $obj->dateOfBirth;
                   $agent->birthPlace = \Normalizer::normalize($obj->placeOfBirth[0]->{"preferredName"},\Normalizer::NFC);
                   $agent->deathDate = $obj->dateOfDeath;
                   $agent->deathPlace = \Normalizer::normalize($obj->placeOfDeath[0]->{"preferredName"},\Normalizer::NFC);
                   foreach ($obj->professionOrOccupation as $prof) {
                      $agent->prof[] = \Normalizer::normalize($prof->{"preferredName"},\Normalizer::NFC);
                   }
                   if ($full) {
                       $agent->bio = $obj->biographicalOrHistoricalInformation;
                       foreach ($obj->placeOfActivity as $poa) {
                          $agent->placeOfActivity[] = $poa->{"preferredName"};
                       }
                   }
               } else {
                   $agent->establishment = $obj->dateOfEstablishment[0];
                   $agent->termination = $obj->dateOfTermination[0];
                   foreach ($obj->topic as $top) {
                      $agent->topic[] = \Normalizer::normalize($top->{"preferredName"},\Normalizer::NFC);
                   }
                   if ($full) {
                   foreach ($obj->placeOfBusiness as $pob) {
                        $agent->placeOfBusiness[] = \Normalizer::normalize($pob->{"preferredName"},\Normalizer::NFC);
                   }
                  }
               }
               if ($full) {
                   foreach ($obj->sameAs as $link) {
                        $id = $link->{"@id"};
                        $name = \Normalizer::normalize($link->collection->name,\Normalizer::NFC);
                        $icon = $link->collection->icon;
                        $agent->sameAs[] = [$id,$icon,$name];
                    }
                    $agent->variants = $obj->variantName;
               }
             }
             //var_dump($obj);
           return $agent;
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
         * Get a raw LCCN (not normalized).  Returns false if none available.
         *
         * @return string|bool
         */
        public function getRawLCCN()
        {
            $lccn = $this->getFirstFieldValue('010');
            if (!empty($lccn)) {
                return $lccn;
            }
            $lccns = $this->getFieldArray('700', ['0']);
            foreach ($lccns as $lccn) {
                if (substr($lccn, 0, '5') == '(DLC)') {
                    return substr($lccn, 5);
                }
            }
            return false;
        }
    }
