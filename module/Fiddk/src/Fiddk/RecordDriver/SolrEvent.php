<?php
/**
 * Model for EDM authority records in Solr.
 *
 * PHP version 7
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
 *
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
namespace Fiddk\RecordDriver;

/**
 * Model for EDM authority records in Solr.
 *
 * @category VuFind
 *
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
class SolrEvent extends SolrAuthDefault
{

   /**
    * Returns the type of the event
    */
    public function getEventType()
    {
        return isset($this->fields['event_type'])
              ? $this->fields['event_type'] : [];
    }

    /**
     * Returns the type of the event
     */
     public function getEventDate()
     {
         return isset($this->fields['date'])
               ? $this->fields['date'] : [];
     }

     /**
      * Returns the type of the event
      */
      public function getEventPlace()
      {
          return isset($this->fields['geographic'])
                ? $this->fields['geographic'] : [];
      }

      /**
       * Returns related links
       */
       public function getsameAs()
       {
           return isset($this->fields['related'])
                 ? $this->fields['related'] : [];
       }

       /**
        * Get related works
        */
        public function getWorks()
        {
          $works = isset($this->fields['work']) ? $this->fields['work'] : [];
          $work_ids = isset($this->fields['work_id']) ? $this->fields['work_id'] : [];
          return array_combine($work_ids,$works);
        }

      /**
       * Returns the agents of the event
       */
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

}
