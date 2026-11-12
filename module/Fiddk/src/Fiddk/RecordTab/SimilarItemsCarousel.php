<?php

/**
 * Similar items carousel record tab.
 *
 * PHP version 8
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
 * along with this program; if not, see
 * <https://www.gnu.org/licenses/>.
 *
 * @category VuFind
 * @package  RecordTabs
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_tabs Wiki
 */

namespace Fiddk\RecordTab;

use VuFindSearch\Command\SimilarCommand;
use VuFindSearch\ParamBag;

/**
 * Similar items carousel record tab.
 *
 * @category VuFind
 * @package  RecordTabs
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_tabs Wiki
 */
class SimilarItemsCarousel extends \VuFind\RecordTab\AbstractBase
{
    /**
     * Constructor
     *
     * @param \VuFindSearch\Service $search Search service
     * @param array                 $config Configuration array
     */
    public function __construct(
        protected \VuFindSearch\Service $search,
        protected array $config = []
    ) {
    }

    /**
     * Get the on-screen description for this tab.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Similar Items';
    }

    /**
     * Get the similar records for the current record.
     *
     * @return \VuFindSearch\Response\RecordCollectionInterface
     */
    public function getResults()
    {
        $driver = $this->getRecordDriver();
        $params = isset($this->config['Record']['similar_items_carousel_count'])
            ? new ParamBag(['rows' => $this->config['Record']['similar_items_carousel_count']])
            : null;
        $command = new SimilarCommand(
            $driver->getSourceIdentifier(),
            $driver->getUniqueId(),
            $params
        );
        return $this->search->invoke($command)->getResult();
    }
}
