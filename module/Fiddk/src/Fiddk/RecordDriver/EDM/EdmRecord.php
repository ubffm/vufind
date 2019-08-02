<?php

/**
 * Parser for EDM records
 *
 * PHP version 5
 *
 *
 * @category  File_Formats
 * @package   EDM
 * @author    Julia Beck <j.beck@ub.uni-frankfurt.de>
 */

/**
 * Represents a single EDM record
 *
 * An EDM record contains core and zero or more contextual classes with one or more properties.
 *
 */

namespace Fiddk\RecordDriver\EDM;

class EdmRecord
{

    protected $record;

    protected $belongsTo = [
      "edm:dataProvider" => "ore:Aggregation",
      "edm:isShownAt" => "ore:Aggregation",
      "dm2e:hasAnnotatableVersionAt" => "ore:Aggregation",
      "edm:hasView" => "ore:Aggregation",
      "edm:isRelatedTo" => "edm:ProvidedCHO",
      "edm:wasPresentAt" => "edm:ProvidedCHO",
      "dm2e:publishedAt" => "edm:ProvidedCHO",
      "dm2e:callNumber" => "edm:ProvidedCHO",
      "dcterms:issued" => "edm:ProvidedCHO",
      "dcterms:alternative" => "edm:ProvidedCHO",
      "bibo:volume" => "edm:ProvidedCHO",
      "dcterms:temporal" => "edm:ProvidedCHO",
      "dcterms:extent" => "edm:ProvidedCHO",
      "dcterms:tableOfContents" => "edm:ProvidedCHO",
      "dcterms:spatial" => "edm:ProvidedCHO",
      "dc:date" => "edm:ProvidedCHO",
      "dc:subject" => "edm:ProvidedCHO",
      "dcterms:provenance" => "edm:ProvidedCHO",
    ];

    /**
     * create a new SimpleXML EDM Record with all namespaces registered
     *
     * @return true
     */
    function __construct($edm = null)
    {
        if ($this->record === null) {
          $this->record = new \SimpleXMLElement($edm);
          if ($this->record->getNamespaces(true)) {
            foreach ($this->record->getDocNamespaces(true) as $ns => $nsLink) {
              $this->record->registerXPathNamespace($ns,$nsLink);
            }
          } else {
            //TODO: Exception instead
            var_dump("something went wrong");
          }
        }

        return $this->record;
    }

    /**
     * Destroys the class field
     */
    function __destruct()
    {
        $this->record = null;
    }

    // get all classes
    function getClasses() {
      return $this->record->xpath("/rdf:RDF/*");
    }

    // convenience method to get certain class directly
    function getClassesOfType($classType = "") {
      return $this->record->xpath("/rdf:RDF/".$classType);
    }

    function getClassProperties($class = null) {
      return $class->xpath("./*");
    }

    function getPropertiesOfClass($classType = "") {
      return $this->record->xpath("/rdf:RDF/".$classType."/*");
    }

    function getAttributeVal($elem = null, $ns = "", $attr = "") {
      $attr = $elem->attributes($ns,true)->$attr;
      return isset($attr) ? $attr->__toString() : "";
    }

    function getAttrVal($str = "") {
      $attrs = [];
      try {
        $props = $this->record->xpath("/rdf:RDF/edm:ProvidedCHO/".$str);
        foreach ($props as $prop) {
          $attr = $prop->attributes("rdf",true);
          $attrs[] = isset($attr["resource"]) ? $attr["resource"]->__toString() : "";

        }
      } catch (\Exception $e) {
        return [];
      }
      return $attrs;
    }

    function getValuesOf($props = null, $propType = "", $fieldName = "") {
      $values = [];
      $parts = explode(':',$propType);
      $ns = $parts[0];
      $name = $parts[1];
      foreach ($props as $prop) {
        if ($prop->getName() == $name && array_key_exists($ns,$prop->getNamespaces())) {
          // literal value
          if ($prop->__toString()) {
            $values[] = $prop->__toString();
          // linked resource
          } else {
            $values[] = $this->findMatchingPropVals($prop,$fieldName);
          }
        }
      }
      return $values;
    }

    function getPropValues($propType = "") {
      $vals = [];
      $classType = $this->belongsTo[$propType];
      $props = $this->getPropertiesOfClass($classType);
      if ($props) {
        $vals = $this->getValuesOf($props,$propType,"skos:prefLabel");
      }
      return $vals;
    }

    function getLinkedPropValues($propType = "", $labelField = "", $fieldName = "") {
      $vals = [];
      $classType = $this->belongsTo[$propType];
      $props = $this->getPropertiesOfClass($classType);
      if ($props) {
        $labels = $this->getValuesOf($props,$propType, $labelField);
        $links = $this->getValuesOf($props,$propType, $fieldName);
        $vals = array_combine($links,$labels);
      }
      return $vals;
    }

    function findMatchingPropVals($prop = null, $fieldName = "") {
      $vals = [];
      $matchingProps = [];
      $attrVal = $this->getAttributeVal($prop,"rdf","resource");
      if ($fieldName && $attrVal) {
        $matchingProps = array_unique($this->record->xpath('/rdf:RDF/*[@rdf:about="'.$attrVal.'"]/' . $fieldName));
        if ($matchingProps) {
          foreach ($matchingProps as $matchingProp) {
            if ($matchingProp->__toString()) {
              $vals[] = $matchingProp->__toString();
            }
            else {
              $vals[] = $this->findMatchingPropVals($matchingProp,"skos:prefLabel");
            }
          }
          return implode(", ",$vals);
        }
      }
      // no matching properties
      return $attrVal;
    }

    function toXML() {
      return $this->record->asXML();
    }

}
