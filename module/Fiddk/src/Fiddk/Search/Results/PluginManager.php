<?php

/**
 * Search results plugin manager
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

namespace Fiddk\Search\Results;

/**
 * Search results plugin manager
 *
 * @category VuFind
 * @package  Search
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
class PluginManager extends \VuFind\Search\Results\PluginManager
{
    /**
     * Default plugin aliases.
     *
     * @var array
     */
    protected $aliases = [
        'VuFind\Search\SolrAuthor\Results' => \Fiddk\Search\SolrAuthor\Results::class,
        'solrauthor' => \Fiddk\Search\SolrAuthor\Results::class,
        'solrevent' => \Fiddk\Search\SolrEvent\Results::class,
        'solrwork' => \Fiddk\Search\SolrWork\Results::class,
        'solrauthority' => \Fiddk\Search\SolrAuthority\Results::class,
    ];

    /**
     * Default plugin factories.
     *
     * @var array
     */
    protected $factories = [
        \Fiddk\Search\SolrAuthor\Results::class =>
            \VuFind\Search\Solr\ResultsFactory::class,
        \Fiddk\Search\SolrEvent\Results::class =>
            \VuFind\Search\Solr\ResultsFactory::class,
        \Fiddk\Search\SolrWork\Results::class =>
            \VuFind\Search\Solr\ResultsFactory::class,
        \Fiddk\Search\SolrAuthority\Results::class =>
            \VuFind\Search\Solr\ResultsFactory::class,
    ];

        /**
     * Constructor
     *
     * Make sure plugins are properly initialized.
     *
     * @param mixed $configOrContainerInstance Configuration or container instance
     * @param array $v3config                  If $configOrContainerInstance is a
     * container, this value will be passed to the parent constructor.
     */
    public function __construct(
        $configOrContainerInstance = null,
        array $v3config = []
    ) {
        parent::__construct($configOrContainerInstance, $v3config);
    }

}