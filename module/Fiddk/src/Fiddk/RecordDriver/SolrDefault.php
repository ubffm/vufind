<?php
/**
 * Model for EDM records in Solr.
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

/**
 * Model for EDM records in Solr.
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
class SolrDefault extends \VuFind\RecordDriver\SolrDefault {
    use Feature\EdmReaderTrait;

    public function getIntermediates()
    {
        return $this->fields['intermediate'] ?? [];
    }

    /**
     * Get an array of all dataproviders, also taking in consideration if
     * there are intermediate data providers.
     *
     * @return array
     */
    public function getInstitutionLinked()
    {
        $inters = $this->getIntermediates();
        $inst = $this->getInstitutions()[0];
        $res = [];
        if (!empty($inters) and $inst != "Projekt „Theater und Musik in Weimar 1754-1990“") {
            foreach ($inters as $inter) {
                if ($inter == "BASE - Bielefeld Academic Search Engine") {
                    $instkey = explode("_", $this->getEdmReader()->getAttrVals("edm:dataProvider", "ore:Aggregation")[0])[1];
                    $instlink = "https://www.base-search.net/Search/Results?q=dccoll:" . $instkey;
                } else {
                    $instlink = $this->getDProvFromConfig($inst,1);
                }
                $interlink = $this->getDProvFromConfig($inter,1);;
                $res = ["inter" => [$inter,$interlink,$inst,$instlink]];
            }
        } else {
            $res = ["inst" => [$inst,$this->getDProvFromConfig($inst,1)]];
        }
        return $res;
    }

    /**
     * Get an array of all dataproviders, also taking in consideration if
     * there are intermediate data providers.
     *
     * @return array
     */
    public function getMoreAboutProvider()
    {
        $inters = $this->getIntermediates();
        $inst = $this->getInstitutions()[0];
        if (!empty($inters) and $inst != "Projekt „Theater und Musik in Weimar 1754-1990“") {
            foreach ($inters as $inter) {
                if ($inter == "BASE - Bielefeld Academic Search Engine") {
                    return "BASE";
                } else {
                    return $this->getDProvFromConfig($inst,2);
                }
            }
        } else {
            return $this->getDProvFromConfig($inst,2);
        }
    }

    /**
     * Get an array of all dataproviders, also taking in consideration if
     * there are intermediate data providers.
     *
     * @return array
     */
    public function getInfoAboutProvider()
    {
        $inst = $this->getInstitutions()[0];
        return $this->getDProvFromConfig($inst,0);
    }

    /**
     * Get an array of all dataproviders, also taking in consideration if
     * there are intermediate data providers.
     *
     * @return array
     */
    protected function getDProvFromConfig($inst,$i)
    {
        $dprovConf = $this->mainConfig->DataProvider;
        $instkey = preg_replace("/\r|\n|\s|,|\/|\(|\)/", "", $inst);
        $info = explode(',', $dprovConf[$instkey]);
        return $info[$i];
    }

}