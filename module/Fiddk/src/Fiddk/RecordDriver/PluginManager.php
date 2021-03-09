<?php
/**
 * Record driver plugin manager
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
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
namespace Fiddk\RecordDriver;

use Laminas\ServiceManager\Factory\InvokableFactory;

/**
 * Record driver plugin manager
 *
 * @category VuFind
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
class PluginManager extends \VuFind\RecordDriver\PluginManager
{
    /**
     * Default plugin aliases.
     *
     * @var array
     */
    protected $aliases = [
        'solredm' => SolrEdm::class,
        'solrauthor' => SolrAuthor::class,
        'solrevent' => SolrEvent::class,
        'solrwork' => SolrWork::class,
    ];

  /**
     * Default plugin factories.
     *
     * @var array
     */
    protected $factories = [
        SolrEdm::class => \VuFind\RecordDriver\SolrDefaultFactory::class,
        SolrAuthDefault::class => \VuFind\RecordDriver\SolrDefaultFactory::class,
        SolrAuthor::class => \VuFind\RecordDriver\SolrDefaultFactory::class,
        SolrEvent::class => \VuFind\RecordDriver\SolrDefaultFactory::class,
        SolrWork::class => \VuFind\RecordDriver\SolrDefaultFactory::class,
    ];

    /**
     * Convenience method to retrieve a populated Solr author record driver.
     *
     * @param array $data Raw Solr data
     *
     * @return AbstractBase
     */
    public function getSolrAuthorRecord($data)
    {
        return $this->getSolrRecord($data, 'SolrAuthor','');
    }

    /**
     * Convenience method to retrieve a populated Solr event record driver.
     *
     * @param array $data Raw Solr data
     *
     * @return AbstractBase
     */
    public function getSolrEventRecord($data)
    {
        return $this->getSolrRecord($data, 'SolrEvent','');
    }

    /**
     * Convenience method to retrieve a populated Solr work record driver.
     *
     * @param array $data Raw Solr data
     *
     * @return AbstractBase
     */
    public function getSolrWorkRecord($data)
    {
        return $this->getSolrRecord($data, 'SolrWork','');
    }
}
