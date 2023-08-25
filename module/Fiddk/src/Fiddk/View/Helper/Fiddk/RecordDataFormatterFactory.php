<?php
/**
 * Factory for record driver data formatting view helper
 *
 * PHP version 7
 *
 * Copyright (C) Villanova University 2016.
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
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:architecture:record_data_formatter
 * Wiki
 */
namespace Fiddk\View\Helper\Fiddk;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

/**
 * Factory for record driver data formatting view helper
 *
 * @category VuFind
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:architecture:record_data_formatter
 * Wiki
 */
class RecordDataFormatterFactory extends \VuFind\View\Helper\Root\RecordDataFormatterFactory
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container     Service manager
     * @param string             $requestedName Service being created
     * @param null|array         $options       Extra options (optional)
     *
     * @return object
     *
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     * creating a service.
     * @throws ContainerException if any other error occurs
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        if (!empty($options)) {
            throw new \Exception('Unexpected options sent to factory.');
        }
        $helper = new $requestedName();
        $helper->setDefaults(
            'collection-info',
            [$this, 'getDefaultCollectionInfoSpecs']
        );
        $helper->setDefaults(
            'collection-record',
            [$this, 'getDefaultCollectionRecordSpecs']
        );
        $helper->setDefaults('core', [$this, 'getDefaultCoreSpecs']);
        $helper->setDefaults('Person', [$this, 'getDefaultPersonSpecs']);
        $helper->setDefaults('PersonGnd', [$this, 'getDefaultPersonGndSpecs']);
        $helper->setDefaults('PersonRelated', [$this, 'getDefaultPersonRelatedSpecs']);
        $helper->setDefaults('Corporation', [$this, 'getDefaultCorporateSpecs']);
        $helper->setDefaults('CorporationGnd', [$this, 'getDefaultCorporateGndSpecs']);
        $helper->setDefaults('CorporationRelated', [$this, 'getDefaultCorporateRelatedSpecs']);
        $helper->setDefaults('Event', [$this, 'getDefaultEventSpecs']);
        $helper->setDefaults('EventGnd', [$this, 'getDefaultEventGndSpecs']);
        $helper->setDefaults('EventRelated', [$this, 'getDefaultEventRelatedSpecs']);
        $helper->setDefaults('Work', [$this, 'getDefaultCoreSpecs']);
        $helper->setDefaults('WorkGnd', [$this, 'getDefaultWorkGndSpecs']);
        $helper->setDefaults('WorkRelated', [$this, 'getDefaultWorkRelatedSpecs']);
        $helper->setDefaults('related', [$this, 'getDefaultRelatedSpecs']);
        $helper->setDefaults('Provider', [$this, 'getDefaultProviderSpecs']);
        $helper->setDefaults('seeAlso', [$this, 'getDefaultSeeAlsoSpecs']);
        return $helper;
    }

    /**
     * Get default specifications for displaying data in core metadata.
     *
     * @return array
     */
    public function getDefaultCoreSpecs()
    {
        $spec = new \VuFind\View\Helper\Root\RecordDataFormatter\SpecBuilder();
        $spec->setLine('Subtitle', 'getSubtitle');
        $spec->setLine('Alternative Title', 'getAlternativeTitles');
        $spec->setLine(
            'New Title',
            'getNewerTitles',
            null,
            ['recordLink' => 'title']
        );
        $spec->setLine(
            'Previous Title',
            'getPreviousTitles',
            null,
            ['recordLink' => 'title']
        );
        $spec->setTemplateLine(
            'In',
            'getContainerTitle',
            'data-containerTitle.phtml'
        );
        $spec->setLine(
            'dc:type',
            'getFormats',
            'RecordHelper',
            ['helperMethod' => 'getFormatList']
        );
        $spec->setTemplateLine(
            'Published',
            'getPublicationDetails',
            'data-publicationDetails.phtml'
        );
        $spec->setTemplateLine(
            'dcterms:created',
            'getCreationDetails',
            'data-placeDateDetails.phtml'
        );
        $spec->setTemplateLine(
            'DatesPlaces',
            'getPlaceDateDetails',
            'data-placeDateDetails.phtml'
        );
        $spec->setLine('dcterms:extent', 'getExtent');
        $spec->setLine('bf:shelfMark', 'getCallNumber');
        $spec->setLine('edm:currentLocation', 'getCurrentLocation');
        $spec->setLine('dcterms:provenance', 'getProvenance');
        $spec->setMultiLine(
            'dc:contributor',
            'getDeduplicatedAuthors',
            $this->getAuthorFunction()
        );
        $spec->setLine('dc:language', 'getLanguages', null, ['translate'=> true, 'translationTextDomain' => 'iso639-2::']);
        $spec->setTemplateLine('dc:description', 'getSummary', 'data-collapsible.phtml');
        $spec->setLine('ISBN', 'getISBNs');
        $spec->setLine('ISSN', 'getISSNs');
        $spec->setLine(
            'Edition',
            'getEdition',
            null,
            ['prefix' => '<span property="bookEdition">', 'suffix' => '</span>']
        );
        $spec->setTemplateLine('Series', 'getSeries', 'data-series.phtml');
        $spec->setLine('Genre', 'getGenres');
        $spec->setTemplateLine(
            'dc:subject',
            'getAllSubjectHeadings',
            'data-allSubjectHeadings.phtml',
            ['context' => ['extended' => true]]
        );
        $spec->setTemplateLine(
            'dcterms:hasPart',
            'getChildRecordCount',
            'data-childRecords.phtml',
            ['allowZero' => false]
        );
        $spec->setLine('Rights', 'getAccessRestrictions');
        $spec->setTemplateLine('Tags', true, 'data-tags.phtml');
        return $spec->getArray();
    }

    /**
     * Get default specifications for displaying data in core metadata.
     *
     * @return array
     */
    public function getDefaultPersonSpecs()
    {
        $spec = new \VuFind\View\Helper\Root\RecordDataFormatter\SpecBuilder();
        $spec->setLine('Alternative', 'getUseFor');
        $spec->setLine('Occupation', 'getOccupation');
        return $spec->getArray();
    }

        /**
     * Get default specifications for displaying event data in core metadata.
     *
     * @return array
     */
    public function getDefaultPersonGndSpecs()
    {
        $spec = new \VuFind\View\Helper\Root\RecordDataFormatter\SpecBuilder();
        $spec->setLine('GndIdentifier', 'getGndIdentifier');
        $spec->setTemplateLine('Entitätstyp', 'getEntityType', 'data-collapsible_str.phtml');
        $spec->setTemplateLine('gndSubjectCategory', 'getGndSubjectCategory', 'data-label-array.phtml');
        $spec->setTemplateLine('Occupation', 'getGndOccuptaions', 'data-label-array.phtml');
        $spec->setTemplateLine('Lebensdaten', 'getGndBirthDeath', 'data-birthDeath.phtml');
        $spec->setTemplateLine('Wirkungsdaten', 'getGndPeriodOfActivity', 'data-collapsible_str.phtml');
        $spec->setTemplateLine('placeOfActivity', 'getGndPlaceOfActivity', 'data-label-array.phtml');
        $spec->setTemplateLine('GeographicAreaCode', 'getGndGeographicAreaCode', 'data-label-array.phtml');
        $spec->setTemplateLine('Gender', 'getGndGenders', 'data-label-array.phtml');
        $spec->setTemplateLine('BiographicalDetails', 'getGndBio', 'data-collapsible_str.phtml');
        $spec->setTemplateLine('Description', 'getGndDescription', 'data-label-array.phtml');
        $spec->setTemplateLine('Publications', 'getGndPublications', 'data-collapsible_str.phtml');
        $spec->setTemplateLine('Instrument', 'getGndInstrument', 'data-label-array.phtml');
        $spec->setTemplateLine('Affiliation', 'getGndAffiliation', 'data-label-array.phtml');
        $spec->setTemplateLine('Homepage', 'getGndHomepage', 'data-id-label-array.phtml');
        $spec->setTemplateLine('Familial Relationship', 'getGndFamilialRel', 'data-label-array.phtml');
        $spec->setTemplateLine('Professional Relationship', 'getGndProfessionalRel', 'data-label-array.phtml');
        $spec->setTemplateLine('Pseudonym', 'getGndPseudonym', 'data-label-array.phtml');
        $spec->setTemplateLine('RealIdentity', 'getGndRealIdentity', 'data-label-array.phtml');
        $spec->setTemplateLine('edm::skos:altLabel', 'getGndVariants', 'data-collapsible_str.phtml');
        return $spec->getArray();
    }

    /**
     * Get default specifications for displaying data in core metadata.
     *
     * @return array
     */
    public function getDefaultPersonRelatedSpecs()
    {
        $spec = new \VuFind\View\Helper\Root\RecordDataFormatter\SpecBuilder();
        // events
        // works
        // records
        return $spec->getArray();
    }

        /**
     * Get default specifications for displaying data in core metadata.
     *
     * @return array
     */
    public function getDefaultCorporateSpecs()
    {
        $spec = new \VuFind\View\Helper\Root\RecordDataFormatter\SpecBuilder();
        $spec->setLine('Alternative', 'getUseFor');
        return $spec->getArray();
    }

       /**
     * Get default specifications for displaying event data in core metadata.
     *
     * @return array
     */
    public function getDefaultCorporateGndSpecs()
    {
        $spec = new \VuFind\View\Helper\Root\RecordDataFormatter\SpecBuilder();
        $spec->setLine('GndIdentifier', 'getGndIdentifier');
        $spec->setTemplateLine('Entitätstyp', 'getEntityType', 'data-collapsible_str.phtml');
        $spec->setTemplateLine('gndSubjectCategory', 'getGndSubjectCategory', 'data-label-array.phtml');
        $spec->setTemplateLine('broaderTermInstantial', 'getGndBroaderTermInstantial', 'data-label-array.phtml');
        $spec->setTemplateLine('established', 'getGndEstablishment', 'data-collapsible_str.phtml');
        $spec->setTemplateLine('terminated', 'getGndTermination', 'data-collapsible_str.phtml');
        $spec->setTemplateLine('placeOfBusiness', 'getGndPlaceOfBusiness', 'data-label-array.phtml');
        $spec->setTemplateLine('spatialAreaOfActivity', 'getGndSpatialAreaOfActivity', 'data-label-array.phtml');
        $spec->setTemplateLine('GeographicAreaCode', 'getGndGeographicAreaCode', 'data-label-array.phtml');
        $spec->setTemplateLine('BiographicalDetails', 'getGndBio', 'data-collapsible_str.phtml');
        $spec->setTemplateLine('Description', 'getGndDescription', 'data-label-array.phtml');
        $spec->setTemplateLine('Homepage', 'getGndHomepage', 'data-id-label-array.phtml');
        $spec->setTemplateLine('precedingCorporateBody', 'getGndPrecedingCorp', 'data-label-array.phtml');
        $spec->setTemplateLine('succeedingCorporateBody', 'getGndSucceedingCorp', 'data-label-array.phtml');
        $spec->setTemplateLine('Abbreviation', 'getGndAbbreviation', 'data-collapsible_str.phtml');
        $spec->setTemplateLine('edm::skos:altLabel', 'getGndVariants', 'data-collapsible_str.phtml');
        return $spec->getArray();
    }

        /**
     * Get default specifications for displaying data in core metadata.
     *
     * @return array
     */
    public function getDefaultCorporateRelatedSpecs()
    {
        $spec = new \VuFind\View\Helper\Root\RecordDataFormatter\SpecBuilder();
        $spec->setMultiLine(
            'dc:contributor',
            'getDeduplicatedAuthors',
            $this->getAuthorFunction()
        );
        return $spec->getArray();
    }

    /**
     * Get default specifications for displaying event data in core metadata.
     *
     * @return array
     */
    public function getDefaultEventSpecs()
    {
        $spec = new \VuFind\View\Helper\Root\RecordDataFormatter\SpecBuilder();
        $spec->setLine('Alternative', 'getUseFor');
        $spec->setLine('Type of Event', 'getEventType');
        $spec->setLine('Genre', 'getGenres');
        $spec->setLine('DateOfEvent', 'getEventDate');
        $spec->setLine('PlaceOfEvent', 'getEventPlace');
        $spec->setLine('edm::dc:description', 'getDescription');
        return $spec->getArray();
    }

    /**
     * Get default specifications for displaying event data in core metadata.
     *
     * @return array
     */
    public function getDefaultEventGndSpecs()
    {
        $spec = new \VuFind\View\Helper\Root\RecordDataFormatter\SpecBuilder();
        $spec->setLine('GndIdentifier', 'getGndIdentifier');
        $spec->setTemplateLine('Entitätstyp', 'getEntityType', 'data-collapsible_str.phtml');
        $spec->setTemplateLine('gndSubjectCategory', 'getGndSubjectCategory', 'data-label-array.phtml');
        $spec->setTemplateLine('DateOfEvent', 'getGndDateOfEvent', 'data-label-array.phtml');
        $spec->setTemplateLine('PlaceOfEvent', 'getGndPlaceOfEvent', 'data-label-array.phtml');
        $spec->setTemplateLine('placeOfActivity', 'getGndSpatialAreaOfActivity', 'data-label-array.phtml');
        $spec->setTemplateLine('GeographicAreaCode', 'getGndGeographicAreaCode', 'data-label-array.phtml');
        $spec->setTemplateLine('BiographicalDetails', 'getGndBio', 'data-collapsible_str.phtml');
        $spec->setTemplateLine('Description', 'getGndDescription', 'data-label-array.phtml');
        $spec->setTemplateLine('Homepage', 'getGndHomepage', 'data-id-label-array.phtml');
        $spec->setTemplateLine('edm::skos:altLabel', 'getGndVariants', 'data-collapsible_str.phtml');
        return $spec->getArray();
    }

    /**
     * Get default specifications for displaying data in core metadata.
     *
     * @return array
     */
    public function getDefaultEventRelatedSpecs()
    {
        $spec = new \VuFind\View\Helper\Root\RecordDataFormatter\SpecBuilder();
        $spec->setMultiLine(
            'dc:contributor',
            'getDeduplicatedAuthors',
            $this->getAuthorFunction()
        );
        $spec->setTemplateLine(
            'RelatedWorksEve',
            'getWorks',
            'data-related.phtml'
        );
        return $spec->getArray();
    }

     /**
     * Get default specifications for displaying event data in core metadata.
     *
     * @return array
     */
    public function getDefaultWorkGndSpecs()
    {
        $spec = new \VuFind\View\Helper\Root\RecordDataFormatter\SpecBuilder();
        $spec->setLine('GndIdentifier', 'getGndIdentifier');
        $spec->setTemplateLine('Entitätstyp', 'getEntityType', 'data-collapsible_str.phtml');
        $spec->setTemplateLine('gndSubjectCategory', 'getGndSubjectCategory', 'data-label-array.phtml');
        $spec->setTemplateLine('BiographicalDetails', 'getGndBio', 'data-collapsible_str.phtml');
        $spec->setTemplateLine('Published', 'getGndDateOfPublication', 'data-collapsible_str.phtml');
        $spec->setTemplateLine('edm::skos:altLabel', 'getGndVariants', 'data-collapsible_str.phtml');
        return $spec->getArray();
    }

    /**
     * Get default specifications for displaying data in core metadata.
     *
     * @return array
     */
    public function getDefaultWorkRelatedSpecs()
    {
        $spec = new \VuFind\View\Helper\Root\RecordDataFormatter\SpecBuilder();
        $spec->setMultiLine(
            'dc:contributor',
            'getDeduplicatedAuthors',
            $this->getAuthorFunction()
        );
        $spec->setTemplateLine(
            'RelatedWorksEve',
            'getWorks',
            'data-related.phtml'
        );
        return $spec->getArray();
    }

    /**
     * Get default specifications for displaying see also metadata.
     *
     * @return array
     */
    public function getDefaultSeeAlsoSpecs()
    {
        $spec = new \VuFind\View\Helper\Root\RecordDataFormatter\SpecBuilder();
        $spec->setTemplateLine('edm:isShownAt', 'getCatalogueLink', 'data-externalLink.phtml');
        $spec->setTemplateLine('edm:isShownAt', 'getLicenseLink', 'data-licenseLink.phtml');
        $spec->setTemplateLine('edm:hasView', 'getDigitalCopies', 'data-collapsible_.phtml');
        $spec->setTemplateLine('edm:wasPresentAt', 'getEvents', 'link-event.phtml');
        $spec->setTemplateLine('Work', 'getWorks', 'link-work.phtml');
        $spec->setTemplateLine('edm:isRelatedTo', 'getAllRecordLinks', 'data-internalLink.phtml');
        return $spec->getArray();
    }

        /**
     * Get default specifications for displaying see also metadata.
     *
     * @return array
     */
    public function getDefaultProviderSpecs()
    {
        $spec = new \VuFind\View\Helper\Root\RecordDataFormatter\SpecBuilder();
        $spec->setTemplateLine('edm:dataProvider', 'getInstitutionLinked', 'data-provLink.phtml');
        $spec->setTemplateLine('Ask Archive', 'askArchive', 'data-askArchive.phtml');
        return $spec->getArray();
    }

    /**
     * Get the callback function for processing authors.
     *
     * @return Callable
     */
    protected function getAuthorFunction()
    {
        return function ($data, $options) {
            // Lookup array of singular/plural labels (note that Other is always
            // plural right now due to lack of translation strings).
            $labels = [
            'primary' => 'dc:contributor',
            'secondary' => 'dc:contributor',
            'corporate' => 'Corporate Author',
            ];
            // Lookup array of schema labels.
            $schemaLabels = [
            'primary' => 'author',
            'secondary' => 'author',
            'corporate' => 'creator'
            ];
            // Lookup array of sort orders.
            $order = ['primary' => 1, 'secondary' => 2, 'corporate' => 3];
            // Sort the data:
            $final = [];
            foreach ($data as $type => $values) {
                $final[] = [
                'label' => $labels[$type],
                'values' => [$type => $values],
                'options' => [
                    'pos' => $options['pos'] + $order[$type],
                    'renderType' => 'RecordDriverTemplate',
                    'template' => 'data-authors.phtml',
                    'context' => [
                        'type' => $type,
                        'schemaLabel' => $schemaLabels[$type],
                        'requiredDataFields' => [
                            ['name' => 'role', 'prefix' => 'edm::']
                        ],
                    ],
                ],
                ];
            }
            return $final;
        };
    }
}
