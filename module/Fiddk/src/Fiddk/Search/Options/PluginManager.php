<?php

/**
 * Search options plugin manager
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

namespace Fiddk\Search\Options;

/**
 * Search options plugin manager
 *
 * @category VuFind
 * @package  Search
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
class PluginManager extends \VuFind\Search\Options\PluginManager
{
    /**
     * Default plugin aliases.
     *
     * @var array
     */
    protected $aliases = [
        'solrauthority' => \Fiddk\Search\SolrAuthority\Options::class,
        'solrperson' => \Fiddk\Search\SolrPerson\Options::class,
        'solrcorporation' => \Fiddk\Search\SolrCorporation\Options::class,
        'solrevent' => \Fiddk\Search\SolrEvent\Options::class,
        'solrwork' => \Fiddk\Search\SolrWork\Options::class,
    ];

    /**
     * Default plugin factories.
     *
     * @var array
     */
    protected $factories = [
        \Fiddk\Search\SolrAuthority\Options::class => \VuFind\Search\Options\OptionsFactory::class,
        \Fiddk\Search\SolrPerson\Options::class => \VuFind\Search\Options\OptionsFactory::class,
        \Fiddk\Search\SolrCorporation\Options::class => \VuFind\Search\Options\OptionsFactory::class,
        \Fiddk\Search\SolrEvent\Options::class => \VuFind\Search\Options\OptionsFactory::class,
        \Fiddk\Search\SolrWork\Options::class => \VuFind\Search\Options\OptionsFactory::class,
    ];

}
