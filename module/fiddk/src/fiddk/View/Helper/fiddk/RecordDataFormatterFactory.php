<?php
/**
 * Factory for record driver data formatting view helper
 *
 * PHP version 5
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
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:architecture:record_data_formatter
 * Wiki
 */
namespace fiddk\View\Helper\fiddk;

/**
 * Factory for record driver data formatting view helper
 *
 * @category VuFind
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:architecture:record_data_formatter
 * Wiki
 */
class RecordDataFormatterFactory extends \VuFind\View\Helper\Root\RecordDataFormatterFactory
{
    /**
     * Create the helper.
     *
     * @return RecordDataFormatter
     */
    public function __invoke()
    {
        $helper = new RecordDataFormatter();
        $helper->setDefaults(
            'collection-info', [$this, 'getDefaultCollectionInfoSpecs']
        );
        $helper->setDefaults(
            'collection-record', [$this, 'getDefaultCollectionRecordSpecs']
        );
        $helper->setDefaults('core', [$this, 'getDefaultCoreSpecs']);
        $helper->setDefaults('result', [$this, 'getDefaultResultSpecs']);
        $helper->setDefaults('description', [$this, 'getDefaultDescriptionSpecs']);
        return $helper;
    }

    /**
     * Get default specifications for displaying data in collection-info metadata.
     *
     * @return array
     */
    public function getDefaultCollectionInfoSpecs()
    {
        $spec = new RecordDataFormatter\SpecBuilder();
        $spec->setTemplateLine(
            'Main Authors', 'getDeduplicatedAuthors', 'data-authors.phtml',
            [
                'useCache' => true,
                'labelFunction' => function ($data) {
                    return count($data['main']) > 1
                        ? 'Main Authors' : 'Main Author';
                },
                'context' => ['type' => 'main', 'schemaLabel' => 'author'],
            ]
        );
        $spec->setTemplateLine(
            'Corporate Authors', 'getDeduplicatedAuthors', 'data-authors.phtml',
            [
                'useCache' => true,
                'labelFunction' => function ($data) {
                    return count($data['corporate']) > 1
                        ? 'Corporate Authors' : 'Corporate Author';
                },
                'context' => ['type' => 'corporate', 'schemaLabel' => 'creator'],
            ]
        );
        $spec->setTemplateLine(
            'Other Authors', 'getDeduplicatedAuthors', 'data-authors.phtml',
            [
                'useCache' => true,
                'context' => [
                    'type' => 'secondary', 'schemaLabel' => 'contributor'
                ],
            ]
        );
        $spec->setLine('Summary', 'getSummary');
        $spec->setLine(
            'Format', 'getFormats', 'RecordHelper',
            ['helperMethod' => 'getFormatList']
        );
        $spec->setLine('Language', 'getLanguages');
        $spec->setTemplateLine(
            'Published', 'getPublicationDetails', 'data-publicationDetails.phtml'
        );
        $spec->setLine(
            'Edition', 'getEdition', null,
            ['prefix' => '<span property="bookEdition">', 'suffix' => '</span>']
        );
        $spec->setTemplateLine('Series', 'getSeries', 'data-series.phtml');
        $spec->setTemplateLine(
            'Subjects', 'getAllSubjectHeadings', 'data-allSubjectHeadings.phtml'
        );
        $spec->setTemplateLine('Online Access', true, 'data-onlineAccess.phtml');
        $spec->setTemplateLine(
            'Related Items', 'getAllRecordLinks', 'data-allRecordLinks.phtml'
        );
        $spec->setLine('Notes', 'getGeneralNotes');
        $spec->setLine('Production Credits', 'getProductionCredits');
        $spec->setLine('ISBN', 'getISBNs');
        $spec->setLine('ISSN', 'getISSNs');
        return $spec->getArray();
    }

    /**
     * Get default specifications for displaying data in collection-record metadata.
     *
     * @return array
     */
    public function getDefaultCollectionRecordSpecs()
    {
        $spec = new RecordDataFormatter\SpecBuilder();
        $spec->setLine('Summary', 'getSummary');
        $spec->setTemplateLine(
            'Main Authors', 'getDeduplicatedAuthors', 'data-authors.phtml',
            [
                'useCache' => true,
                'labelFunction' => function ($data) {
                    return count($data['main']) > 1
                        ? 'Main Authors' : 'Main Author';
                },
                'context' => ['type' => 'main', 'schemaLabel' => 'author'],
            ]
        );
        $spec->setTemplateLine(
            'Corporate Authors', 'getDeduplicatedAuthors', 'data-authors.phtml',
            [
                'useCache' => true,
                'labelFunction' => function ($data) {
                    return count($data['corporate']) > 1
                        ? 'Corporate Authors' : 'Corporate Author';
                },
                'context' => ['type' => 'corporate', 'schemaLabel' => 'creator'],
            ]
        );
        $spec->setTemplateLine(
            'Other Authors', 'getDeduplicatedAuthors', 'data-authors.phtml',
            [
                'useCache' => true,
                'context' => [
                    'type' => 'secondary', 'schemaLabel' => 'contributor'
                ],
            ]
        );
        $spec->setLine('Language', 'getLanguages');
        $spec->setLine(
            'Format', 'getFormats', 'RecordHelper',
            ['helperMethod' => 'getFormatList']
        );
        $spec->setLine('Access', 'getAccessRestrictions');
        $spec->setLine('Related Items', 'getRelationshipNotes');
        return $spec->getArray();
    }

    /**
     * Get default specifications for displaying data in core metadata.
     *
     * @return array
     */
    public function getDefaultCoreSpecs()
    {
        $spec = new RecordDataFormatter\SpecBuilder();
        $spec->setLine(
            'Title', 'getTitle', null, ['recordLink' => 'title']
        );
        $spec->setLine('Subtitle', 'getSubtitle');
        $spec->setLine('Alternative Title', 'getAlternativeTitles');
        $spec->setTemplateLine(
             'In', 'getContainerTitle', 'data-containerTitle.phtml'
        );
        $spec->setLine(
            'Format', 'getFormats', 'RecordHelper',
            ['helperMethod' => 'getFormatList']
        );
        $spec->setTemplateLine(
            'Contributors', 'getAgents', 'data-agents.phtml'
        );
        $spec->setTemplateLine(
            'Other Authors', 'getDeduplicatedAuthors', 'data-authors.phtml',
            [
                'useCache' => true,
                'context' => [
                    'type' => 'secondary',
                    'schemaLabel' => 'contributor',
                    'requiredDataFields' => [
                        ['name' => 'role', 'prefix' => 'CreatorRoles::']
                    ]
                ],
            ]
        );
        $spec->setTemplateLine(
            'ExtraContents', 'getExtraContent', 'data-extraContent.phtml'
        );
        $spec->setLine(
            'Description', 'getSummary'
        );
        $spec->setLine('Language', 'getLanguages', 'Simple',
            ['translate' => true, 'translateTextDomain' => 'iso639-1::']);
        $spec->setTemplateLine(
            'Published', 'getPublicationDetails', 'data-publicationDetails.phtml'
        );
        $spec->setTemplateLine(
            'Dates', 'getDates', 'data-dates.phtml'
        );
        $spec->setLine(
            'Edition', 'getEdition', null,
            ['prefix' => '<span property="bookEdition">', 'suffix' => '</span>']
        );
        $spec->setTemplateLine('Series', 'getSeries', 'data-series.phtml');
        $spec->setTemplateLine(
            'Subjects', 'getAllSubjectHeadings', 'data-allSubjectHeadings.phtml'
        );
        $spec->setTemplateLine(
            'child_records', 'getChildRecordCount', 'data-childRecords.phtml',
            ['allowZero' => false]
        );
        $spec->setTemplateLine('Online Access', true, 'data-onlineAccess.phtml');
        $spec->setTemplateLine(
            'Related Items', 'getAllRecordLinks', 'data-allRecordLinks.phtml'
        );
        $spec->setTemplateLine('Tags', true, 'data-tags.phtml');
        $spec->setLine('Institution', 'getInstitutions');
        $spec->setTemplateLine(
            'View', 'getView', 'data-view.phtml'
        );
        return $spec->getArray();
    }

    /**
     * Get default specifications for displaying data in the description tab.
     *
     * @return array
     */
    public function getDefaultDescriptionSpecs()
    {
        $spec = new RecordDataFormatter\SpecBuilder();
        $spec->setLine('Summary', 'getSummary');
        $spec->setLine('Published', 'getDateSpan');
        $spec->setLine('Item Description', 'getGeneralNotes');
        $spec->setLine('Physical Description', 'getPhysicalDescriptions');
        $spec->setLine('Publication Frequency', 'getPublicationFrequency');
        $spec->setLine('Playing Time', 'getPlayingTimes');
        $spec->setLine('Format', 'getSystemDetails');
        $spec->setLine('Audience', 'getTargetAudienceNotes');
        $spec->setLine('Awards', 'getAwards');
        $spec->setLine('Production Credits', 'getProductionCredits');
        $spec->setLine('Bibliography', 'getBibliographyNotes');
        $spec->setLine('ISBN', 'getISBNs');
        $spec->setLine('ISSN', 'getISSNs');
        $spec->setLine('DOI', 'getCleanDOI');
        $spec->setLine('Related Items', 'getRelationshipNotes');
        $spec->setLine('Access', 'getAccessRestrictions');
        $spec->setLine('Finding Aid', 'getFindingAids');
        $spec->setLine('Publication_Place', 'getHierarchicalPlaceNames');
        $spec->setTemplateLine('Author Notes', true, 'data-authorNotes.phtml');
        return $spec->getArray();
    }

    /**
     * Get default specifications for displaying data in the description tab.
     *
     * @return array
     */
    public function getDefaultResultSpecs()
    {
        $spec = new RecordDataFormatter\SpecBuilder();
        $spec->setTemplateLine(
            'Published', 'getPublicationDetails', 'data-publicationDetails.phtml'
        );
        return $spec->getArray();
    }


}
