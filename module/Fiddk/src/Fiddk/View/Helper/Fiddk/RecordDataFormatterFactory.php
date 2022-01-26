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
namespace Fiddk\View\Helper\Fiddk;

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
        // normally only one of the next three is contained in the data
        $spec->setTemplateLine(
            'Published', 'getPublicationDetails', 'data-datesPlaces.phtml'
        );
        $spec->setTemplateLine(
            'Performed', 'getEventDetails', 'data-datesPlaces.phtml'
        );
        $spec->setTemplateLine(
            'DatesPlaces', 'getOtherDatesPlaces', 'data-datesPlaces.phtml',[
                'labelFunction' => function ($data) {
                $placeFound = false;
                $dateFound = false;
                  foreach ($data as $entry) {
                    if (!empty($entry['dates'])) {
                      $dateFound = true;
                    }
                    if (!empty($entry['places'])) {
                      $placeFound = true;
                    }
                  }
                  if ($dateFound && $placeFound) {
                    return 'DatesPlaces';
                  } elseif ($dateFound) {
                    return 'Date';
                  } else {
                    return 'Place';
                  }
            },]
        );
        $spec->setTemplateLine(
            'AboutObject', 'getAboutObject', 'data-aboutObject.phtml'
        );
        $spec->setTemplateLine(
            'Contributors', 'getAgents', 'data-agents.phtml'
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
        $spec->setTemplateLine('Tags', true, 'data-tags.phtml');
        $spec->setTemplateLine(
            'See also', 'getSeeAlso', 'data-seeAlso.phtml'
        );
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
            'Contributors', 'getAgent', 'data-agent.phtml',[
                'labelFunction' => function ($data) {
                    return key($data);
                }]
        );
        // normally only one of the next three is contained in the data
        $spec->setTemplateLine(
            'Published', 'getPublicationDetails', 'data-datesPlaces.phtml'
        );
        $spec->setTemplateLine(
            'Performed', 'getEventDetails', 'data-datesPlaces.phtml'
        );
        $spec->setTemplateLine(
            'DatesPlaces', 'getOtherDatesPlaces', 'data-datesPlaces.phtml',[
                'labelFunction' => function ($data) {
                $placeFound = false;
                $dateFound = false;
                  foreach ($data as $entry) {
                    if (!empty($entry['dates'])) {
                      $dateFound = true;
                    }
                    if (!empty($entry['places'])) {
                      $placeFound = true;
                    }
                  }
                  if ($dateFound && $placeFound) {
                    return 'DatesPlaces';
                  } elseif ($dateFound) {
                    return 'Date';
                  } else {
                    return 'Place';
                  }
            },]
        );
        $spec->setTemplateLine(
          'edm:isShownAt','getLicenceLink','data-licenceLink'
        );
        return $spec->getArray();
    }

}
