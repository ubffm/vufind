<?php

/**
 * Parser for EDM records
 *
 * PHP version 7
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
    protected $ns;

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
            foreach ($this->record->getDocNamespaces(true) as $names => $namesLink) {
              $this->ns[$names] = $namesLink;
              $this->record->registerXPathNamespace($names,$namesLink);
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

    /* for fields that always return literal values */
    function getLiteralVals($prop = "", $class = "") {
      $name = explode(":",$prop);
      if (array_key_exists($name[0],$this->ns)) {
        return $this->record->xpath("/rdf:RDF/".$class."/".$prop);
      }
      else {
        return [];
      }
    }

    /* for temporal attributes that will never have an associated
    Timespan class */
    function getAttrVals($prop = "", $class = "") {
      $attrs = [];
      $name = explode(":",$prop);
      if (array_key_exists($name[0],$this->ns)) {
        $props = $this->record->xpath("/rdf:RDF/".$class."/".$prop);
        foreach ($props as $prop) {
          $attr = $prop->attributes("rdf",true);
          $attrs[] = isset($attr["resource"]) ? $attr["resource"]->__toString() : "";
        }
        return $attrs;
      }
      else {
        return [];
      }
    }

    /* for properties that return either literals or attribute values that
    have to be resolved */
    function getPropValues($prop = "", $class = "", $fieldName = "") {
      $vals = [];
      $name = explode(":",$prop);
      if (array_key_exists($name[0],$this->ns)) {
        $props = $this->record->xpath("/rdf:RDF/".$class."/".$prop);
        foreach ($props as $prop) {
          $resLink = $this->getResourceVal($prop);
          if ($resLink) {
            if (in_array($name[1], ["temporal","issued", "created"])) {
              $vals[] = $resLink;
            } else {
              $vals[] = $this->findMatchingPropVal($resLink, $fieldName);
            }
          }
          else {
            $vals[] = $prop->__toString();
          }
        }
        return $vals;
      }
      else {
        return [];
      }
    }

    function findMatchingPropVal($attr = "", $fieldName = "") {
        return implode(array_unique($this->record->xpath('/rdf:RDF/*[@rdf:about="'.$attr.'"]/' . $fieldName)),", ");
    }

    function getResourceVal($prop = null) {
      $attrs = $prop->attributes("rdf",true);
      return isset($attrs["resource"]) ? $attrs["resource"]->__toString() : "";
    }

    function getLinkedPropValues($prop = "", $class = "", $fieldName = "") {
      $vals = [];
      $name = explode(":",$prop);
      if (array_key_exists($name[0],$this->ns)) {
        $props = $this->record->xpath("/rdf:RDF/".$class."/".$prop);
        $links = [];
        foreach ($props as $p) {
          $links[] = $this->getResourceVal($p);
        }
        $labels = $this->getPropValues($prop, $class, $fieldName);
        if (count($links) == count($labels)) {
          $vals = array_combine($links,$labels);
        }
      }
      return $vals;
    }

    function toXML() {
      return $this->record->asXML();
    }

}
