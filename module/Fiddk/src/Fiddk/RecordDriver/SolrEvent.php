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
 * @author  Demian Katz <demian.katz@villanova.edu>
 * @author  Ere Maijala <ere.maijala@helsinki.fi>
 * @author  Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
namespace Fiddk\RecordDriver;

/**
 * Model for EDM authority records in Solr.
 *
 * @category VuFind
 *
 * @author  Demian Katz <demian.katz@villanova.edu>
 * @author  Ere Maijala <ere.maijala@helsinki.fi>
 * @author  Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
class SolrEvent extends SolrAuthDefault
{

    /**
     * Get icon for this entity type.
     *
     * @return string
     */
    public function getRecordIcon()
    {
        return 'icons/calendar4-event.svg';
    }


    /**
     * Convenience method for event date
     */
    public function getEventDate()
    {
        return $this->fields['date'] ?? [];
    }

    /**
     * Convenience method for event place
     */
    public function getEventPlace()
    {
        return $this->fields['geographic'] ?? [];
    }

    /**
     * Get related works
     */
    public function getWorks()
    {
        $works = $this->fields['work'] ?? [];
        $work_ids = $this->fields['work_id'] ?? [];
        return array_combine($work_ids, $works);
    }

    /**
     * Get genres
     */
    public function getGenres()
    {
        return $this->fields['genre'] ?? [];
    }

    /**
     * Get place of Gnd event
     *
     * @return array
     */
    public function getGndPlaceOfEvent()
    {
        return $this->extraDetails['placeOfConferenceOrEvent'] ?? [];
    }

    /**
     * Get date of Gnd event
     *
     * @return array
     */
    public function getGndDateOfEvent()
    {
        return $this->extraDetails['dateOfConferenceOrEvent'] ?? [];
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
        return $this->fields['author_id'] ?? [];
    }

    /**
     * Get the number of work records related to this event
     *
     * @return int Number of records
     */
    public function getWorkCount()
    {
        $id = $this->getUniqueId();
        $query = new \VuFindSearch\Query\Query(
            'event_id:"' . $id . '"'
        );
        // Disable highlighting for efficiency; not needed here:
        $params = new \VuFindSearch\ParamBag(['hl' => ['false']]);
        $command = new \VuFindSearch\Command\SearchCommand("SolrWork", $query, 0, 0, $params);
        return $this->searchService->invoke($command)->getResult()->getTotal();
    }

    /**
     * Returns links to provider
     */
    public function getSameAs()
    {
        return $this->getEdmRecord()->getAttrVals("owl:sameAs", "edm:Event");
    }

    public function getEventRelatedWorkCount()
    {
        return parent::getRelatedWorkCount('event_id');
    }

    public function getEventRelatedResourceCount()
    {
        return parent::getRelatedResourceCount(['event_id','topic_id']);
    }
}
