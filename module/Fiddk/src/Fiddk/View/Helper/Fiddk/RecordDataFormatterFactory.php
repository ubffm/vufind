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
use Laminas\ServiceManager\Factory\FactoryInterface;
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
     public function __invoke(ContainerInterface $container, $requestedName,
         array $options = null
     ) {
         if (!empty($options)) {
             throw new \Exception('Unexpected options sent to factory.');
         }
         $helper = new $requestedName();
         $helper->setDefaults(
             'collection-info', [$this, 'getDefaultCollectionInfoSpecs']
         );
         $helper->setDefaults(
             'collection-record', [$this, 'getDefaultCollectionRecordSpecs']
         );
         $helper->setDefaults('core', [$this, 'getDefaultCoreSpecs']);
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
            'New Title', 'getNewerTitles', null, ['recordLink' => 'title']
        );
        $spec->setLine(
            'Previous Title', 'getPreviousTitles', null, ['recordLink' => 'title']
        );
        $spec->setTemplateLine(
            'In', 'getContainerTitle', 'data-containerTitle.phtml'
        );
        $spec->setLine(
            'dc:type', 'getFormats', 'RecordHelper',
            ['helperMethod' => 'getFormatList']
        );
        $spec->setTemplateLine(
            'Published', 'getPublicationDetails', 'data-publicationDetails.phtml'
        );
        $spec->setTemplateLine(
            'DatesPlaces', 'getPlaceDateDetails', 'data-placeDateDetails.phtml'
        );
        $spec->setLine('dcterms:extent', 'getExtent');
        $spec->setLine('bf:shelfMark', 'getCallNumber');
        $spec->setLine('dcterms:provenance', 'getProvenance');
        $spec->setMultiLine(
            'dc:contributor', 'getDeduplicatedAuthors', $this->getAuthorFunction()
          );
        $spec->setLine('dc:language', 'getLanguages', null, ['translate'=> true, 'translationTextDomain' => 'iso639-1::']);
        $spec->setTemplateLine('dc:description', 'getSummary', 'data-collapsible.phtml');
        $spec->setLine('ISBN', 'getISBNs');
        $spec->setLine('ISSN', 'getISSNs');
        $spec->setLine(
            'Edition', 'getEdition', null,
            ['prefix' => '<span property="bookEdition">', 'suffix' => '</span>']
        );
        $spec->setTemplateLine('Series', 'getSeries', 'data-series.phtml');
        $spec->setTemplateLine(
            'dc:subject', 'getAllSubjectHeadings', 'data-allSubjectHeadings.phtml', ['context' => ['extended' => true]]
        );
        $spec->setTemplateLine(
            'dcterms:hasPart', 'getChildRecordCount', 'data-childRecords.phtml',
            ['allowZero' => false]
        );
        $spec->setLine('Rights', 'getAccessRestrictions');
        $spec->setTemplateLine('Tags', true, 'data-tags.phtml');
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
      $spec->setTemplateLine('edm:dataProvider', 'getInstitutionLinked','data-provLink.phtml');
      $spec->setTemplateLine('Ask Archive', 'askArchive','data-askArchive.phtml');
      $spec->setTemplateLine('edm:isShownAt', 'getLicenseLink','data-licenseLink.phtml');
      $spec->setTemplateLine('dm2e:hasAnnotatableVersionAt', 'getCatalogueLink','data-externalLink.phtml');
      $spec->setTemplateLine('edm:isShownBy', 'getDigitalCopies','data-collapsible_.phtml');
      $spec->setTemplateLine('edm:hasView', 'getDigitalCopies','data-collapsible_.phtml');
      $spec->setTemplateLine('edm:wasPresentAt', 'getEvents','link-event.phtml');
      $spec->setTemplateLine('edm:isRelatedTo', 'getAllRecordLinks','data-internalLink.phtml');
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
