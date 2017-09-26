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

        public function loadEntityFacts($id)
        {
           $id = explode('_', $id)[1];
           $json = [];
           try {
               $json = @file_get_contents($this->entityLink . $id);
               $json = json_decode($json);
           } catch (Exception $e) {
               // does not exist
           };
           return $json;
        }

        public function getAgentFacts($id,$full) {
          if (explode('_', $id)[0] == 'gnd') {
            return $this->getEntityFacts($id,$full);
          } else {
            return $this->getFactsFromIndex($id,$full);
          }
        }

        public function getEntityFacts($id,$full) {

            $agent = new Agent();
            $obj = $this->loadEntityFacts($id);
            if ($obj) {
               $agent->name = \Normalizer::normalize($obj->preferredName,\Normalizer::NFC);
               $agent->depiction = isset($obj->depiction->{"thumbnail"}->{"@id"}) ? $obj->depiction->{"thumbnail"}->{"@id"} : '';
               if ($obj->{"@type"} == 'person') {
                   $agent->birthDate = isset($obj->dateOfBirth) ? $obj->dateOfBirth : '';
                   $agent->birthPlace = isset($obj->placeOfBirth[0]->{"preferredName"}) ?
                     \Normalizer::normalize($obj->placeOfBirth[0]->{"preferredName"},\Normalizer::NFC) : '';
                   $agent->deathDate = isset($obj->dateOfDeath) ? $obj->dateOfDeath : '';
                   $agent->deathPlace = isset($obj->placeOfDeath[0]->{"preferredName"}) ?
                     \Normalizer::normalize($obj->placeOfDeath[0]->{"preferredName"},\Normalizer::NFC) : '';
                   if (isset($obj->professionOrOccupation)) {
                   foreach ($obj->professionOrOccupation as $prof) {
                      $agent->prof[] = isset($prof->{"preferredName"}) ?
                        \Normalizer::normalize($prof->{"preferredName"},\Normalizer::NFC) : '';
                   }
               }
                   if ($full) {
                       $agent->bio = isset($obj->biographicalOrHistoricalInformation) ? $obj->biographicalOrHistoricalInformation : '';
                       if (isset($obj->placeOfActivity)) {
                       foreach ($obj->placeOfActivity as $poa) {
                          $agent->placeOfActivity[] = isset($poa->{"preferredName"}) ? $poa->{"preferredName"} : '';
                       }
                   }
                   }
               } else {
                   $agent->establishment = isset($obj->dateOfEstablishment[0]) ? $obj->dateOfEstablishment[0] : '';
                   $agent->termination = isset($obj->dateOfTermination[0]) ? $obj->dateOfTermination[0] : '';
                   if (isset($obj->topic)) {
                   foreach ($obj->topic as $top) {
                     $agent->topic[] = isset($top->{"preferredName"}) ? \Normalizer::normalize($top->{"preferredName"},\Normalizer::NFC) : '';
                   }
               }
                   if ($full) {
                     if (isset($obj->placeOfBusiness)) {
                   foreach ($obj->placeOfBusiness as $pob) {
                        $agent->placeOfBusiness[] = \Normalizer::normalize($pob->{"preferredName"},\Normalizer::NFC);
                   }
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
                    $agent->variants = isset($obj->variantName) ? $obj->variantName : [];
               }
             }
           return $agent;
        }

        public function getFactsFromIndex($id,$full) {

            $agent = new Agent();
            $agent->name = $this->getTitle();

            $this->getFullRecord();
            $this->getEDMClasses();
            $dates = $this->getBirthDeathDate();
            $agent->birthDate = isset($dates["birthDate"]) ? $dates["birthDate"] : '';
            $agent->deathDate = isset($dates["deathDate"]) ? $dates["deathDate"] : '';

            $agent->birthPlace = $this->getBirthPlace();
            $agent->deathPlace = $this->getDeathPlace();
            $agent->prof = $this->getOccupation();

            if ($full) {
                $agent->bio = implode('; ',$this->getBioInfo());
                $agent->variants = $this->getUseFor();
            }

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
         * Get the occupation.
         *
         * @return array
         */
        public function getOccupation()
        {
            return isset($this->fields['occupation'])
                && is_array($this->fields['occupation'])
                ? $this->fields['occupation'] : [];
        }

        /**
         * Get the biographical info.
         *
         * @return array
         */
        public function getBioInfo()
        {
            return isset($this->fields['bioInfo'])
                && is_array($this->fields['bioInfo'])
                ? $this->fields['bioInfo'] : [];
        }

        /**
         * Get the birth date
         *
         * @return array
         */
        public function getBirthDeathDate()
        {
            $persons = isset($this->classes["foaf:Person"])? $this->classes["foaf:Person"] : [];
            $timespans = isset($this->classes["edm:TimeSpan"])? $this->classes["edm:TimeSpan"] : [];
            $retval = [];
            foreach ($persons as $person) {
              foreach ($person as $elem) {
                 $name = $elem->nodeName;
                 if ($name == 'rdaGr2:dateOfBirth') {
                     $retval["birthDate"] = $this->getResourceOrLiteral($elem,$timespans);
            }
            if ($name == 'rdaGr2:dateOfDeath') {
                $retval["deathDate"] = $this->getResourceOrLiteral($elem,$timespans);
       }
         }
      }
      return $retval;
        }

        /**
         * Get the birth place
         *
         * @return array
         */
        public function getBirthPlace()
        {
            return isset($this->fields['birth_place'])
                ? $this->fields['birth_place'] : '';
        }

        /**
         * Get the death place
         *
         * @return array
         */
        public function getDeathPlace()
        {
            return isset($this->fields['death_place'])
                ? $this->fields['death_place'] : '';
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
