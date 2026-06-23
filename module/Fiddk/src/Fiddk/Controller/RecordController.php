<?php

/**
 * Record Controller
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
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */

namespace Fiddk\Controller;

/**
 * Record Controller
 *
 * Fängt fehlende Records ab und liefert eine saubere 404-Seite.
 *
 * @category VuFind
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class RecordController extends \VuFind\Controller\RecordController
{
    /**
     * Home (default) action -- forward to requested (or default) tab.
     *
     * @return mixed
     */
    public function homeAction()
    {
        try {
            return parent::homeAction();
        } catch (\VuFind\Exception\RecordMissing $e) {
            return $this->notFoundAction();
        }
    }

    /**
     * AJAX tab action -- render a tab without surrounding context.
     *
     * @return mixed
     */
    public function ajaxtabAction()
    {
        try {
            return parent::ajaxtabAction();
        } catch (\VuFind\Exception\RecordMissing $e) {
            return $this->notFoundAction();
        }
    }
}
