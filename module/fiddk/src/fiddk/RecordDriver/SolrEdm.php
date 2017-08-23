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
     * Get an array of relevant dates for the record.
     *
     * @return array
     */
    public function getDates()
    {
      $chos = $this->classes["edm:ProvidedCHO"];
      $timespans = $this->classes["edm:TimeSpan"];
      $dates = [];

      foreach ($chos as $cho) {
         foreach ($cho as $elem) {
            switch ($elem->nodeName) {
               case 'dcterms:created':
                  $dates["created"][] = $this->getResourceOrLiteral($elem,$timespans);
                  break;
               case 'dcterms:temporal':
                  $dates["temporal"][] = $this->getResourceOrLiteral($elem,$timespans);
                  break;
               default:
                  break;
            }
         }
      }
      return $dates;
    }

    /**
     * Get an array of publication dates for the record.
     *
     * @return array
     */
    public function getPublicationDates()
    {
      $chos = isset($this->classes["edm:ProvidedCHO"])? $this->classes["edm:ProvidedCHO"] : [];
      $timespans = isset($this->classes["edm:TimeSpan"])? $this->classes["edm:TimeSpan"] : [];
      $pDates = [];

      foreach ($chos as $cho) {
         foreach ($cho as $elem) {
            if ($elem->nodeName == 'dcterms:issued') {
               $pDates[] = $this->getResourceOrLiteral($elem,$timespans);
            }
         }
      }
      return $pDates;
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
     * Get an array of publication places for the record.
     *
     * @return array
     */
    public function getPublicationPlaces()
    {
      $chos = $this->classes["edm:ProvidedCHO"];
      if (array_key_exists("edm:Place", $this->classes)) {
         $edmPlaces = $this->classes["edm:Place"];
      } else {
         $edmPlaces = [];
      }
      $pPlaces = [];

      foreach ($chos as $cho) {
         foreach ($cho as $elem) {
            if ($elem->nodeName == 'dm2e:publishedAt') {
               if ($edmPlaces) {
                  $pPlaces[] = $this->getResourceOrLiteral($elem,$edmPlaces);
               } else {
                  $pPlaces[] = $elem->nodeValue;
               }
            }
         }
      }
      return $pPlaces;
    }

    /**
     * Get an array of publication detail lines combining information from
     * getPublicationDates(), getPublishers() and getPlacesOfPublication().
     *
     * @return array
     */
    public function getPublicationDetails()
    {
    $places = $this->getPublicationPlaces();
    $names = $this->getPublishers();
    $dates = $this->getPublicationDates();

    $i = 0;
    $retval = [];
    while (isset($places[$i]) || isset($names[$i]) || isset($dates[$i])) {
        // Build objects to represent each set of data; these will
        // transform seamlessly into strings in the view layer.
        $retval[] = new \VuFind\RecordDriver\Response\PublicationDetails(
            isset($places[$i]) ? $places[$i] : '',
            isset($names[$i]) ? $names[$i] : '',
            isset($dates[$i]) ? $dates[$i] : ''
        );
        $i++;
    }

    return $retval;
    }

    /**
     * Get an array of extent descriptions for the record.
     *
     * @return array
     */
    public function getExtraContent()
    {
      $chos = $this->classes["edm:ProvidedCHO"];
      $contents = [];

      foreach ($chos as $cho) {
         foreach ($cho as $elem) {
            switch ($elem->nodeName) {
               case 'dcterms:extent':
                  $contents["extent"][] = $elem->nodeValue;
                  break;
               case 'bibo:isbn':
                  $contents["isbn"][] = $elem->nodeValue;
                  break;
               case 'bibo:issn':
                  $contents["issn"][] = $elem->nodeValue;
                  break;
               default:
                  break;
            }
         }
      }
      return $contents;
    }

    /**
     * Get an array of backlinks to data provider content for the record.
     *
     * @return array
     */
    public function getView()
    {
      $aggs = $this->classes["ore:Aggregation"];
      $web = isset($this->classes["edm:WebResource"])? $this->classes["edm:WebResource"] : [];
      $views = [];

      foreach ($aggs as $agg) {
         foreach ($agg as $elem) {
            if ($elem->nodeName == 'edm:hasView') {
               $views[] = $this->getWebResource($elem,$web);
            }
         }
      }
      return $views;
    }

    /**
     * Get an array of data providers.
     *
     * @return array
     */
    public function getDataProviders()
    {
        return isset($this->fields['dataProvider']) ?
            $this->fields['dataProvider'] : [];
    }

    /**
     * Get an array of agents.
     *
     * @return array
     */
    public function getAgents()
    {
      $chos = $this->classes["edm:ProvidedCHO"];
      $agents = array_merge(
         isset($this->classes["foaf:Person"])? $this->classes["foaf:Person"] : [],
         isset($this->classes["foaf:Organization"])? $this->classes["foaf:Organization"] : [],
         isset($this->classes["edm:Agent"])? $this->classes["edm:Agent"] : []);
      $contents = [];
      $agentRoles = ['dc:contributor' => true,'dc:creator' => true,'pro:author' => true,'eclap:director' => true
         ,'eclap:actor' => true,'eclap:setDesigner' => true,'eclap:costumeDesigner' => true,'eclap:performer' => true
         ,'eclap:choreographer' => true,'eclap:dancer' => true];

      foreach ($chos as $cho) {
         foreach ($cho as $elem) {
            $name = $elem->nodeName;
            if (array_key_exists($name, $agentRoles)) {
               $contents[$elem->nodeName][] = [$this->getAgentID($elem),$this->getResourceOrLiteral($elem,$agents)];
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

    /**
     * Get an array of lines from the table of contents.
     *
     * @return array
     */
    public function getTOC()
    {
        // Return empty array if we have no table of contents:
        $fields = isset($this->fields['contents']) ?
            $this->fields['contents'] : [];
        if (!$fields) {
           return [];
        }
        // If we got this far, we have a table -- collect it as a string:
        $toc = [];
        foreach ($fields as $field) {
            // Break the string into appropriate chunks, filtering empty strings,
            // and merge them into return array:
            $toc = array_merge(
                $toc,
                array_filter(explode('--', $field), 'trim')
            );
        }
        return $toc;
     }

     public function checkExistence($containerID,$containerSource) {

       try {
   			$this->loader->load($containerID,$containerSource);
   			return $containerID;
   		} catch (\VuFind\Exception\RecordMissing $e) {
   			return false;
   		}

     }

}
