<?php
/**
 * Author aspect of the Search Multi-class (Options)
 *
 * PHP version 7
 *
 * Copyright (C) Villanova University 2010.
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
 * @package  Search_SolrAuthor
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace Fiddk\Search\SolrAuthorityInfo;
/**
 * Author Search Options
 *
 * @category VuFind
 * @package  Search_SolrAuthor
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class Options extends \VuFind\Search\Solr\Options
{
    /**
     * Constructor
     *
     * @param \VuFind\Config\PluginManager $configLoader Config loader
     */
    public function __construct(\VuFind\Config\PluginManager $configLoader)
    {
        parent::__construct($configLoader);
        // No spell check or highlighting needed when searching by id:
        $this->spellcheck = false;
        $this->highlight = false;
    }

    /**
     * Load all recommendation settings from the relevant ini file.  Returns an
     * associative array where the key is the location of the recommendations (top
     * or side) and the value is the settings found in the file (which may be either
     * a single string or an array of strings).
     *
     * @param string $handler Name of handler for which to load specific settings.
     *
     * @return array associative: location (top/side/etc.) => search settings
     */
    public function getRecommendationSettings($handler = null)
    {
        // Load the necessary settings to determine the appropriate recommendations
        // module:
        $ss = $this->configLoader->get($this->getSearchIni());
        // Load the AuthorModuleRecommendations configuration if available, use
        // standard defaults otherwise:
        if (isset($ss->AuthorityModuleRecommendations)) {
            $recommend = [];
            foreach ($ss->AuthorityModuleRecommendations as $section => $content) {
                $recommend[$section] = [];
                foreach ($content as $current) {
                    $recommend[$section][] = $current;
                }
            }
            if (null !== $handler
                && isset($ss->SideRecommendations->$handler)
                ) {
                  $recommend['side'] = $ss->SideRecommendations
                          ->$handler->toArray();
            } else {
              $recommend['side']
                = isset($ss->General->default_side_recommend)
                    ? $ss->General->default_side_recommend->toArray()
                    : false;
            }
            if (null !== $handler
                && isset($ss->NoResultsRecommendations->$handler)
                ) {
                  $recommend['noresults'] = $ss->NoResultsRecommendations
                          ->$handler->toArray();
            } else {
              $recommend['noresults']
                = isset($ss->General->default_noresults_recommend)
                    ? $ss->General->default_noresults_recommend->toArray()
                    : false;
            }
            return $recommend;
        } else {
            return parent::getRecommendationSettings($handler);
        }
    }
}
