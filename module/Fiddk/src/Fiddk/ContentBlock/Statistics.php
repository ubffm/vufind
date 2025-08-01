<?php
/**
 * Statistics content block.
 *
 * PHP version 7
 *
 * Copyright (C) Villanova University 2018.
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
 * @package  ContentBlock
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:recommendation_modules Wiki
 */
namespace Fiddk\ContentBlock;

use VuFind\Search\Results\PluginManager as ResultsManager;

/**
 * FacetList content block.
 *
 * @category VuFind
 * @package  ContentBlock
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:recommendation_modules Wiki
 */
class Statistics implements \VuFind\ContentBlock\ContentBlockInterface
{
    /**
     * Reuslts plugin manager
     *
     * @var ResultsManager
     */
    protected $resultsManager;

    /**
     * Constructor
     *
     * @param ResultsManager $fcm Facet cache plugin manager
     */
    public function __construct(ResultsManager $rm)
    {
        $this->ResultsManager = $rm;
    }

    /**
     * Store the configuration of the content block.
     *
     * @param string $settings Settings from searches.ini.
     *
     * @return void
     */
    public function setConfig($settings)
    {
    }

    /**
     * Return context variables used for rendering the block's template.
     *
     * @return array
     */
    public function getContext()
    {
        $rresult = $this->ResultsManager->get("Solr");
        $aresult = $this->ResultsManager->get("SolrPerson");
        $eresult = $this->ResultsManager->get("SolrEvent");
        $wresult = $this->ResultsManager->get("SolrWork");
        $rresults = $rresult->getResultTotal();
        $aresults = $aresult->getResultTotal();
        $eresults = $eresult->getResultTotal();
        $wresults = $wresult->getResultTotal();
        return [
            'statistics' => ['Resources' => $rresults,
                             'Agents' => $aresults,
                             'Events' => $eresults,
                             'Works' => $wresults]
        ];
    }
}
