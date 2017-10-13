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
use VuFind\Exception\ILS as ILSException,
    VuFind\View\Helper\Root\RecordLink,
    VuFind\XSLT\Processor as XSLTProcessor;

/**
 * Model for EDM records in Solr.
 *
 * @category VuFind
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
class SolrEdm extends \VuFind\RecordDriver\SolrDefault
{

   protected $fullRecord = NULL;

   protected $classes = [];
   protected $loader = NULL;

   protected $agentRoles = ['dc:contributor' => true,'dc:creator' => true,'pro:author' => true,'eclap:director' => true
      ,'eclap:actor' => true,'eclap:setDesigner' => true,'eclap:costumeDesigner' => true,'eclap:performer' => true
      ,'eclap:choreographer' => true, 'eclap:dramaturge'=> true, 'eclap:composer' => true, 'eclap:dancer' => true
      ,'gndo:photographer' => true, 'pro:illustrator' => true, 'bibo:editor' => true, 'bibo:recipient' => true];

   public function __construct(\Zend\Config\Config $config, $recordConfig= NULL,
     $searchSettings = NULL, \VuFind\Record\Loader $loader
   ) {
      // for checking existence before creating links
      parent::__construct($config);
      $this->loader = $loader;
   }

   /**
    * Get full record.
    *
    * @return array
    */
   public function getFullRecord()
   {
      $this->fullRecord = new \DOMDocument();
      if ($this->fields["fullrecord"]) {
         // Not necessary anymore: '<?xml version="1.0" encoding="UTF-8"?&gt;' .
         $success = $this->fullRecord->loadXML($this->fields["fullrecord"]);
         if (!$success) {
            throw new \Exception("Cannot process fullRecord field!");
         }
      }
      return $this->fullRecord;
   }

   /**
    * Get content of EDM classes ore:Aggregation, edm:ProvidedCHO, ...
    * keys are tagName and the about attribute to make lookup easier
    * @return array
    */
   public function getEDMClasses()
   {
      foreach ($this->fullRecord->childNodes[0]->childNodes as $children) {
         $tagName = $children->tagName;
         $about = $children->getAttribute("rdf:about");
         if (!array_key_exists($tagName, $this->classes)) {
            $this->classes[$tagName] = [];
         }
         $this->classes[$tagName][$about] = $children->childNodes;
      }
      return $this->classes;
   }

   /**
    * Get resource and search for prefLabel or literal
    * @return array
    */
   public function getResourceOrLiteral($elem,$class)
   {
      $resource = $elem->getAttribute("rdf:resource");
      $literal = '';
      if($resource != '') {
         foreach ($class as $about => $contents) {
            if ($resource == $about) {
               foreach ($contents as $content) {
                  if ($content->nodeName == 'skos:prefLabel') {
                     $literal = $content->nodeValue;
                  }
               }
            }
         }
      } else {
         $literal = $elem->nodeValue;
      }
      return $literal;
   }

   /**
    * Get resource about (link)
    * @return array
    */
   public function getWebResource($elem,$class)
   {
      $resource = $elem->getAttribute("rdf:resource");
      $literal = '';
      if($resource != '') {
         foreach ($class as $about => $contents) {
            if ($resource == $about) {
               foreach ($contents as $content) {
                  if ($content->nodeName == 'dc:description') {
                     $literal = $content->nodeValue;
                  }
               }
         }
         }
      }
      return [$resource => $literal];
   }

    /**
     * Get an array of previous titles for the record.
     *
     * @return array
     */
    public function getAlternativeTitles()
    {
        return isset($this->fields['title_alt']) ?
            $this->fields['title_alt'] : [];
    }

    /**
     * Not using this at the moment
     *
     * @return array
     */
    public function getDefaultOpenUrlParams()
    {
    }

    /**
     * Not using this at the moment
     *
     * @return array
     */
    public function getOpenUrl($overrideSupportsOpenUrl = false)
    {
      return false;
    }

    /**
     * Not using this at the moment
     *
     * @return array
     */
    public function getBookOpenUrlParams()
    {
    }

    /**
     * Get an array of lines combining information about place and time.
     *
     *
     * @return array
     */
    public function getEventDetails()
    {
      return $this->getDatesPlaces('Performed');
    }

    /**
     * Get an array of lines combining information about place and time.
     *
     *
     * @return array
     */
    public function getPublicationDetails()
    {
      return $this->getDatesPlaces('Published');
    }

    /**
     * Get an array of lines combining information about place and time.
     *
     *
     * @return array
     */
    public function getOtherDatesPlaces()
    {
      return $this->getDatesPlaces('Other');
    }

    /**
     * Get an array of lines combining information about place and time.
     * Used for all types of place and time given (publication, event or other)
     *
     * @return array
     */
    public function getDatesPlaces($kind)
    {
    $chos = isset($this->classes["edm:ProvidedCHO"])? $this->classes["edm:ProvidedCHO"] : [];
    $edmPlaces = isset($this->classes["edm:Place"])? $this->classes["edm:Place"] : [];
    $orgs = isset($this->classes["foaf:Organization"])? $this->classes["foaf:Organization"] : [];
    $placesOrgs = array_merge($edmPlaces,$orgs);
    $timespans = isset($this->classes["edm:TimeSpan"])? $this->classes["edm:TimeSpan"] : [];
    $retval = [];

    $datesPlaces = ['names' => [], 'dates' => [], 'places' => []];

    foreach ($chos as $cho) {
      foreach ($cho as $elem) {
         switch ($kind) {
            case 'Published':
               $datesPlaces['names'] = $this->getPublishers();
               switch ($elem->nodeName) {
                  case 'dcterms:issued':
                     $datesPlaces['dates'][] = $this->getResourceOrLiteral($elem,$timespans);
                     break;
                  case 'dm2e:publishedAt':
                     $datesPlaces['places'][] = $this->getResourceOrLiteral($elem,$edmPlaces);
                     break;
                  default:
                  break;
               }
               break;
            case 'Other':
               switch ($elem->nodeName) {
                  case 'dc:date':
                  case 'dcterms:temporal':
                  case 'dcterms:created':
                     $datesPlaces['dates'][] = $this->getResourceOrLiteral($elem,$timespans);
                     break;
                  case 'dcterms:spatial':
                     $datesPlaces['places'][] = $this->getResourceOrLiteral($elem,$edmPlaces);
                     break;
                  default:
                  break;
               }
               break;
            case 'Performed':
               switch ($elem->nodeName) {
                 case 'eclap:firstPerformanceDate':
                 case 'eclap:performanceDate':
                    $datesPlaces['dates'][] = $this->getResourceOrLiteral($elem,$timespans);
                    break;
                 case 'eclap:firstPerformancePlace':
                 case 'eclap:performancePlace':
                    $datesPlaces['places'][] = $this->getResourceOrLiteral($elem,$placesOrgs);
                    break;
                 default:
                 break;
               }
               break;
            default:
               break;
         }
      }
   }

   if (count(array_filter($datesPlaces)) != 0) {
      $retval[] = $datesPlaces;
   }

    return $retval;
    }

    /**
     * Get an array of descriptions about the record.
     *
     * @return array
     */
    public function getAboutObject()
    {
      $chos = $this->classes["edm:ProvidedCHO"];
      $contents = [];

      foreach ($chos as $cho) {
         foreach ($cho as $elem) {
            switch ($elem->nodeName) {
               case 'dcterms:extent':
               case 'bibo:isbn':
               case 'bibo:issn':
               case 'bibo:volume':
               case 'dcterms:provenance':
               case 'dcterms:tableOfContents':
               case 'dm2e:callNumber':
                  $contents[$elem->nodeName][] = $elem->nodeValue;
                  break;
               default:
                  break;
            }
         }
      }

      $languages = $this->getLanguages();
      $descriptions = $this->getSummary();
      if ($languages) {
         $contents['dc:language'] = $languages;
      }
      if ($descriptions) {
         $contents['dc:description'] = $descriptions;
      }

      return $contents;
    }

    /**
     * Get an array of formats for the record. Don't use the facet field here.
     *
     * @return array
     */
    public function getFormats()
    {
      $chos = isset($this->classes["edm:ProvidedCHO"])? $this->classes["edm:ProvidedCHO"] : [];
      $concepts = isset($this->classes["skos:Concept"])? $this->classes["skos:Concept"] : [];
      $contents = [];

      foreach ($chos as $cho) {
         foreach ($cho as $elem) {
           if ($elem->nodeName == 'dc:type') {
             $contents[] = $this->getResourceOrLiteral($elem,$concepts);
           }
      }
    }
    return $contents;
  }

  /**
   * Get parent version of format method.
   *
   * @return array
   */
  public function getParentFormats()
  {
     $formats = array_unique(parent::getFormats());
    return $formats;
}

    /**
     * Get an array of backlinks to data provider content for the record.
     *
     * @return array
     */
    public function getSeeAlso()
    {
      $aggs = $this->classes["ore:Aggregation"];

      $webs = isset($this->classes["edm:WebResource"])? $this->classes["edm:WebResource"] : [];
      $orgs = isset($this->classes["foaf:Organization"])? $this->classes["foaf:Organization"] : [];
      $chos = isset($this->classes["edm:ProvidedCHO"])? $this->classes["edm:ProvidedCHO"] : [];
      $seeAlso = ['edm:dataProvider' => false, 'edm:isShownAt' => false, 'dm2e:hasAnnotatableVersionAt' => false,
      'edm:hasView' => false, 'edm:isRelatedTo' => false];

      foreach ($aggs as $agg) {
         foreach ($agg as $elem) {
            switch ($elem->nodeName) {
               case 'edm:hasView':
                  $seeAlso['edm:hasView'][] = $this->getWebResource($elem,$webs);
                  break;
               case 'edm:isShownAt':
                  $webR = $this->getWebResource($elem,$webs);
                  if ($webR != '') {
                     if (current($webR) == 'Inhaltsverzeichnis') {
                        $seeAlso['dcterms:tableOfContents'][] = $webR;
                     } else {
                        $seeAlso['edm:isShownAt'][] = $webR;
                     }
                  }
                  break;
              case 'dm2e:hasAnnotatableVersionAt':
                  $seeAlso['dm2e:hasAnnotatableVersionAt'][] = $this->getWebResource($elem,$webs);
                  break;
               case 'edm:dataProvider':
                  $seeAlso['edm:dataProvider'][] = $this->getDataProvider($elem,$orgs);
                  break;
               default:
                  break;
            }
         }
      }

      foreach ($chos as $cho) {
         foreach ($cho as $elem) {
            switch ($elem->nodeName) {
               case 'edm:isRelatedTo':
                 $resource = $elem->getAttribute("rdf:resource");
                 $resourceID = explode('/',$resource);
                 $seeAlso['edm:isRelatedTo'][] = [$resource => $this->checkExistenceReturnTitleFormat(end($resourceID), 'Solr')];
                  break;
               default:
                  break;
            }
         }
      }

      return array_filter($seeAlso);
    }

    public function checkExistenceReturnTitleFormat($containerID, $containerSource) {

		try {
			$edmObject = $this->loader->load($containerID,$containerSource);
			$str = $edmObject->getTitle() . ' (' . implode(', ',$edmObject->getParentFormats()) . ')';
			return $str;
		} catch (\VuFind\Exception\RecordMissing $e) {
			return '';
		}
	}

    /**
     * Get homepage and prefLabel of data providers.
     *
     * @return array
     */
    public function getDataProvider($elem,$class)
    {
      $resource = $elem->getAttribute("rdf:resource");
      $literal = '';
      $homepage = '';
      if($resource != '') {
         foreach ($class as $about => $contents) {
            if ($resource == $about) {
               foreach ($contents as $content) {
                  switch ($content->nodeName) {
                     case 'skos:prefLabel':
                        $literal = $content->nodeValue;
                        break;
                     case 'foaf:homepage':
                        $homepage = $content->getAttribute("rdf:resource");
                        break;
                     default:
                        break;
                  }
               }
            }
         }
      }
      return [$homepage => $literal];
    }

    /**
     * Get an array of agents.
     *
     * @return array
     */
    public function getAgents()
    {
      $chos = isset($this->classes["edm:ProvidedCHO"]) ? $this->classes["edm:ProvidedCHO"] : [];
      $agents = array_merge(
         isset($this->classes["foaf:Person"]) ? $this->classes["foaf:Person"] : [],
         isset($this->classes["foaf:Organization"]) ? $this->classes["foaf:Organization"] : [],
         isset($this->classes["edm:Agent"]) ? $this->classes["edm:Agent"] : []);
      $contents = [];

      foreach ($chos as $cho) {
         foreach ($cho as $elem) {
            $name = $elem->nodeName;
            if (array_key_exists($name, $this->agentRoles)) {
               $resOrLit = $this->getResourceOrLiteral($elem,$agents);
               if ($resOrLit) {
                 $contents[$elem->nodeName][] = [$this->getAgentID($elem),$resOrLit];
               }
            }
         }
      }
      return $contents;
    }

    /**
     * Get first Agent.
     *
     * @return array
     */
    public function getAgent()
    {
      $chos = isset($this->classes["edm:ProvidedCHO"]) ? $this->classes["edm:ProvidedCHO"] : [];
      $agents = array_merge(
         isset($this->classes["foaf:Person"])? $this->classes["foaf:Person"] : [],
         isset($this->classes["foaf:Organization"])? $this->classes["foaf:Organization"] : [],
         isset($this->classes["edm:Agent"])? $this->classes["edm:Agent"] : []);
      $contents = [];
      $notFound = true;

      foreach ($chos as $cho) {
         foreach ($cho as $elem) {
            if ($notFound) {
               $name = $elem->nodeName;
               if (array_key_exists($name, $this->agentRoles)) {
                  $contents[$elem->nodeName][] = [$this->getAgentID($elem),$this->getResourceOrLiteral($elem,$agents)];
                  $notFound = false; // break
               }

            }
         }
      }
      return $contents;
    }

    /**
    * Get agent ID for search purposes
    * @return string
    */
    public function getAgentID($elem)
    {
      $resource = $elem->getAttribute("rdf:resource");
      if($resource != '') {
         $resParts = explode('/',$resource);
         if ($resParts[4] == 'gnd') {
            $resource = 'gnd_' . $resParts[5];
         } else {
            $resource = array_pop($resParts);
         }
      }
      return $resource;
    }

     public function getThumbnail($size = 'small')
     {
         if (isset($this->fields['thumbnail']) && $this->fields['thumbnail']) {
             return $this->fields['thumbnail'];
         }

     }

     public function getLicenceLink() {

        $licenceLink = [];
        $institution = $this->getInstitutions()[0];

        if ($institution == 'transcript Verlag' or $institution == 'Alexander Street Press') {
           $aggs = $this->classes["ore:Aggregation"];
           $webs = isset($this->classes["edm:WebResource"])? $this->classes["edm:WebResource"] : [];

           foreach ($aggs as $agg) {
             foreach ($agg as $elem) {
                switch ($elem->nodeName) {
                   case 'edm:isShownAt':
                      $licenceLink[] = $this->getWebResource($elem,$webs);
                      break;
                   default:
                      break;
                }
             }
          }
        }
        return $licenceLink;
    }

     public function checkExistence($containerID,$containerSource) {

       try {
   			$this->loader->load($containerID,$containerSource);
   			return $containerID;
   		} catch (\VuFind\Exception\RecordMissing $e) {
   			return false;
   		}

     }

     public function getDM2EXML() {
       return $this->fields['fullrecord'];
     }

}
