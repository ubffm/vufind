<?php
/**
 * Model for EDM authority person records in Solr.
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

use phpDocumentor\Reflection\DocBlock\Tags\Var_;

/**
 * Model for EDM authority person records in Solr.
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
class SolrPerson extends SolrAuthDefault
{

    /**
     * Get icon for this entity type.
     *
     * @return string
     */
    public function getRecordIcon()
    {
        return 'fa-user';
    }


    /**
     * Get the occupation of the person.
     *
     * @return array
     */
    public function getOccupation()
    {
        return $this->fields['occupation'] ?? [];
    }

    /**
     * Get the occupation of the person from GND.
     *
     * @return array
     */
    public function getGndOccuptaions()
    {
        // special case... for reasons...
        if($this->fields['id'] == "gnd_1043940596") {
            return [0 => ['label' => 'Autor'], 1 => ['label' => 'Dramatiker'],
            2 => ['label' => 'Schauspieler'], 3 => ['label' => 'Regisseur'],
            4 => ['label' => 'Drehbuchautor']];
        } else {
            return $this->extraDetails['professionOrOccupation'] ?? [];
        }
    }

    
    /**
     * Get birth date of person or date of establishment of organization.
     *
     * @return string
     */
    public function getBirthDate()
    {
        return $this->fields['birth_date'] ?? '';
    }

    /**
     * Get birth date of Gnd person
     *
     * @return array
     */
    public function getGndBirthDeath()
    {
        return ['dateOfBirth' => $this->getGndBirthDate(), 'dateOfDeath' => $this->getGndDeathDate(),
                'placeOfBirth' => $this->getGndPlaceOfBirth(), 'placeOfDeath' => $this->getGndPlaceOfDeath()];
    }

    /**
     * Get birth date of Gnd person
     *
     * @return array
     */
    public function getGndBirthDate()
    {
        return $this->extraDetails['dateOfBirth'] ?? [];
    }

    /**
     * Get death date of person or date of termination of organization.
     *
     * @return string
     */
    public function getDeathDate()
    {
        return $this->fields['death_date'] ?? '';
    }

    /**
     * Get death date of Gnd person
     *
     * @return array
     */
    public function getGndDeathDate()
    {
        return $this->extraDetails['dateOfDeath'] ?? [];
    }

    /**
     * Get homepage of person
     *
     * @return string
     */
    public function getHomepage()
    {
        return $this->fields['homepage'] ?? '';
    }

    /**
     * Get gender of person.
     */
    public function getGender()
    {
        return $this->getEdmRecord()->getLiteralVals("rdau:P60531", "foaf:Person");
    }

    /**
     * Get gender of Gnd person
     *
     * @return array
     */
    public function getGndGenders()
    {
        return $this->extraDetails['gender'] ?? [];
    }

    /**
     * Get publications of Gnd person
     *
     * @return array
     */
    public function getGndPublications()
    {
        return $this->extraDetails['publication'] ?? [];
    }

    /**
     * Get played instrument of Gnd person
     *
     * @return array
     */
    public function getGndInstrument()
    {
        return $this->extraDetails['playedInstrument'] ?? [];
    }

    /**
     * Get pseudonym of Gnd person
     *
     * @return array
     */
    public function getGndPseudonym()
    {
        return $this->extraDetails['pseudonym'] ?? [];
    }

    /**
     * Get real identity of Gnd person
     *
     * @return array
     */
    public function getGndRealIdentity()
    {
        return $this->extraDetails['realIdentity'] ?? [];
    }

    /**
     * Get period of activity of person.
     */
    public function getGndPeriodOfActivity()
    {
        return $this->extraDetails['periodOfActivity'] ?? [];
    }

    /**
     * Get place of activity of person.
     */
    public function getGndPlaceOfActivity()
    {
        return $this->extraDetails['placeOfActivity'] ?? [];
    }

    /**
     * Get affiliation of person.
     */
    public function getGndAffiliation()
    {
        return $this->extraDetails['affiliation'] ?? [];
    }

    /**
     * Get homepage of person.
     */
    public function getGndHomepage()
    {
        return $this->extraDetails['homepage'] ?? [];
    }

    /**
     * Get familial relationships of person.
     */
    public function getGndFamilialRel()
    {
        return $this->extraDetails['familialRelationship'] ?? [];
    }

    /**
     * Get professional relationships of person.
     */
    public function getGndProfessionalRel()
    {
        return $this->extraDetails['professionalRelationship'] ?? [];
    }

    /**
     * Get place of birth of person.
     */
    public function getPlaceOfBirth()
    {
        return $this->getEdmRecord()->getPropValues("rdau:P60593", "foaf:Person");
    }

    /**
     * Get place of birth of Gnd person
     *
     * @return array
     */
    public function getGndPlaceOfBirth()
    {
        return $this->extraDetails['placeOfBirth'] ?? [];
    }

    /**
     * Get place of death of person.
     */
    public function getPlaceOfDeath()
    {
        return $this->getEdmRecord()->getPropValues("rdau:P60592", "foaf:Person");
    }

    /**
     * Get place of death of Gnd person
     *
     * @return array
     */
    public function getGndPlaceOfDeath()
    {
        return $this->extraDetails['placeOfDeath'] ?? [];
    }

    /**
     * Returns links to provider
     */
    public function getSameAs()
    {
        return $this->getEdmRecord()->getAttrVals("owl:sameAs", "foaf:Person");
    }

    public function getPersonRelatedEventCount()
    {
        return parent::getRelatedEventCount('author_id');
    }

    public function getPersonRelatedWorkCount()
    {
        return parent::getRelatedWorkCount('author_id');
    }

    public function getPersonRelatedResourceCount()
    {
        return parent::getRelatedResourceCount(['author_id','topic_id']);
    }
}
