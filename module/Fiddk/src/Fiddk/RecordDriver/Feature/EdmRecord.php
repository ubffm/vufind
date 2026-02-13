<?php

/**
 * Parser for EDM records
 *
 * PHP version 7
 *
 * @category File_Formats
 * @package  EDM
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 */

/**
 * Represents a single EDM record
 *
 * An EDM record contains core and zero or more contextual classes with one or more properties.
 */
namespace Fiddk\RecordDriver\Feature;

class EdmRecord
{
    protected $edmRecord;

    protected $ns;

    protected $baseUrl = "http://performing-arts.eu/";

    /**
     * create a new SimpleXML EDM Record with all namespaces registered
     *
     * @return true
     */
    public function __construct($edm = null)
    {
        if ($this->edmRecord === null) {
            $this->edmRecord = new \SimpleXMLElement($edm);
            $known = [
                'rdf'    => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
                'rdfs'   => 'http://www.w3.org/2000/01/rdf-schema#',
                'edm'    => 'https://www.europeana.eu/schemas/edm/',
                'dc'     => 'http://purl.org/dc/elements/1.1/',
                'dcterms'=> 'http://purl.org/dc/terms/',
                'ore'    => 'http://www.openarchives.org/ore/terms/',
            ];
            // erst Dokument-Namespaces sammeln …
            $this->ns = [];
            foreach ($this->edmRecord->getDocNamespaces(true) as $p => $uri) {
                if ($p === '') { continue; } // leeres Prefix überspringen (falls Default-NS)
                $this->ns[$p] = $uri;
                $this->edmRecord->registerXPathNamespace($p, $uri);
            }

            // … dann bekannte ergänzen (ohne bestehende zu überschreiben)
            foreach ($known as $p => $uri) {
                if (!isset($this->ns[$p])) {
                    $this->ns[$p] = $uri;
                    $this->edmRecord->registerXPathNamespace($p, $uri);
                }
            }
            if ($this->edmRecord->getNamespaces(true)) {
                foreach ($this->edmRecord->getDocNamespaces(true) as $names => $namesLink) {
                    $this->ns[$names] = $namesLink;
                    $this->edmRecord->registerXPathNamespace($names, $namesLink);
                }
            } else {
                throw new \Exception("Namespace error");
            }
        }

        return $this->edmRecord;
    }

    /**
     * Destroys the class field
     */
    public function __destruct()
    {
        $this->edmRecord = null;
    }

    /* for fields that always return literal values */
    public function getLiteralVals($prop = "", $class = "")
    {
        $name = explode(":", $prop);
        if (array_key_exists($name[0], $this->ns)) {
            return $this->edmRecord->xpath("/rdf:RDF/" . $class . "/" . $prop);
        } else {
            return [];
        }
    }

    /* for temporal attributes that will never have an associated
    Timespan class */
    public function getAttrVals($prop = "", $class = "")
    {
        $attrs = [];
        $name = explode(":", $prop);
        if (array_key_exists($name[0], $this->ns)) {
            $props = $this->edmRecord->xpath("/rdf:RDF/" . $class . "/" . $prop);
            foreach ($props as $prop) {
                $attr = $prop->attributes("rdfs", true);
                if ($attr) {
                    $attrs[] = isset($attr["label"]) ? $attr["label"]->__toString() : "";
                } else {
                    $attr = $prop->attributes("rdf", true);
                    $attrs[] = isset($attr["resource"]) ? $attr["resource"]->__toString() : "";
                }
            }
            return $attrs;
        } else {
            return [];
        }
    }

    /* for properties that return either literals or attribute values that
    have to be resolved */
    public function getPropValues($prop = "", $class = "", $fieldName = "")
    {
        $vals = [];
        $name = explode(":", $prop);
        if (array_key_exists($name[0], $this->ns)) {
            $props = $this->edmRecord->xpath("/rdf:RDF/" . $class . "/" . $prop);
            foreach ($props as $prop) {
                $resLink = $this->getResourceVal($prop);
                if ($resLink) {
                    if ($class == "ore:Aggregation" or str_starts_with($resLink,$this->baseUrl)) {
                        $vals[] = $this->findMatchingPropVal($resLink, $fieldName);
                    }
                } else if (in_array($name[1], ["temporal","issued", "created"])) {
                    $label = $this->getLabel($prop);
                    if ($label) {
                      $vals[] = $label; 
                    } else {
                      $vals[] = $resLink;
                    }
                } else {
                    $vals[] = $prop->__toString();
                }
            }
            return $vals;
        } else {
            return [];
        }
    }

    public function findMatchingPropVal($attr = "", $fieldName = "")
    {
        return implode(", ", array_unique($this->edmRecord->xpath('/rdf:RDF/*[@rdf:about="' . $attr . '"]/' . $fieldName)));
    }

    public function getResourceVal($prop = null)
    {
        $attrs = $prop->attributes("rdf", true);
        return isset($attrs["resource"]) ? $attrs["resource"]->__toString() : "";
    }

    public function getLabel($prop = null)
    {
        $attrs = $prop->attributes("rdfs", true);
        return isset($attrs["label"]) ? $attrs["label"]->__toString() : "";
    }

    public function getLinkedPropValues($prop = "", $class = "", $fieldName = "")
    {
        $vals = [];
        $name = explode(":", $prop);
        if (array_key_exists($name[0], $this->ns)) {
            $props = $this->edmRecord->xpath("/rdf:RDF/" . $class . "/" . $prop);
            $links = [];
            foreach ($props as $p) {
                $links[] = $this->getResourceVal($p);
            }
            $labels = $this->getPropValues($prop, $class, $fieldName);
            if (count($links) == count($labels)) {
                $vals = array_combine($links, $labels);
            }
        }
        return $vals;
    }

    public function toXML()
    {
        return $this->edmRecord->asXML();
    }
}
