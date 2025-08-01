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
        'solrperson' => SolrPerson::class,
        'solrcorporation' => SolrCorporation::class,
        'solrevent' => SolrEvent::class,
        'solrwork' => SolrWork::class,
        \VuFind\RecordDriver\SolrDefault::class => \Fiddk\RecordDriver\SolrDefault::class,
        \VuFind\RecordDriver\SolrAuthDefault::class => \Fiddk\RecordDriver\SolrAuthDefault::class,
    ];

    /**
     * Default plugin factories.
     *
     * @var array
     */
    protected $factories = [
        SolrEdm::class => \VuFind\RecordDriver\SolrDefaultFactory::class,
        \Fiddk\RecordDriver\SolrDefault::class => \VuFind\RecordDriver\SolrDefaultFactory::class,
        \Fiddk\RecordDriver\SolrAuthDefault::class => \VuFind\RecordDriver\SolrDefaultFactory::class,
        SolrPerson::class => \VuFind\RecordDriver\SolrDefaultFactory::class,
        SolrCorporation::class => \VuFind\RecordDriver\SolrDefaultFactory::class,
        SolrEvent::class => \VuFind\RecordDriver\SolrDefaultFactory::class,
        SolrWork::class => \VuFind\RecordDriver\SolrDefaultFactory::class,
    ];

    /**
     * Convenience method to retrieve a populated Solr person record driver.
     *
     * @param array $data Raw Solr data
     *
     * @return AbstractBase
     */
    public function getSolrPersonRecord($data)
    {
        return $this->getSolrRecord($data, 'SolrPerson', '');
    }

    /**
     * Convenience method to retrieve a populated Solr corporation record driver.
     *
     * @param array $data Raw Solr data
     *
     * @return AbstractBase
     */
    public function getSolrCorporationRecord($data)
    {
        return $this->getSolrRecord($data, 'SolrCorporation', '');
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
        return $this->getSolrRecord($data, 'SolrEvent', '');
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
        return $this->getSolrRecord($data, 'SolrWork', '');
    }
}
