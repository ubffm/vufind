<?php

/**
 * Blender aspect of the Search Multi-class (Results)
 *
 * PHP version 7
 *
 * Copyright (C) The National Library of Finland 2015-2019.
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
 * @package  Search_Blender
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Page
 */

namespace Fiddk\Search\Blender;

/**
 * Blender aspect of the Search Multi-class (Results)
 *
 * @category VuFind
 * @package  Search_Blender
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Page
 */
class Results extends \VuFind\Search\Blender\Results
{
    /**
     * 
     *
     * @return String
     */
    public function getEntityType($searchClass)
    {
        $entityType = $searchClass ?? '';
        switch ($entityType) {
        case "SolrEdm":
            return "Record";
            break;
        case "SolrPerson":
            return "Person";
            break;
        case "SolrCorporation":
            return "Corporation";
            break;
        case "SolrEvent":
            return "Event";
            break;
        case "SolrWork":
            return "Work";
            break;
        default:
            return "AllRecord";
        }
    }
}
