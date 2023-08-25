<?php

/**
 * Search params plugin manager
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
 * @package  Search
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */

namespace Fiddk\Search\Params;

/**
 * Search params plugin manager
 *
 * @category VuFind
 * @package  Search
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
class PluginManager extends \VuFind\Search\Params\PluginManager
{
    /**
     * Default plugin aliases.
     *
     * @var array
     */
    protected $aliases = [
        'blender' => \VuFind\Search\Blender\Params::class,
        'solrauthority' => \Fiddk\Search\SolrAuthority\Params::class,
        'solrperson' => \Fiddk\Search\SolrPerson\Params::class,
        'solrcorporation' => \Fiddk\Search\SolrCorporation\Params::class,
        'solrevent' => \Fiddk\Search\SolrEvent\Params::class,
        'solrwork' => \Fiddk\Search\SolrWork\Params::class,
        \VuFind\Search\SolrAuth\Params::class => \Fiddk\Search\SolrAuthority\Params::class,
    ];

}
