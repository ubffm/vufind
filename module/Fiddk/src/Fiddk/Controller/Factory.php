<?php
/**
 * Factory for controllers.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2014.
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind2
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
namespace Fiddk\Controller;
use Zend\ServiceManager\ServiceManager;

/**
 * Factory for controllers.
 *
 * @category VuFind2
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:developer_manual Wiki
 */
class Factory
{

    /**
    * Construct a generic controller.
    *
    * @param string         $name Name of table to construct (fully qualified
    * class name, or else a class name within the current namespace)
    * @param ServiceManager $sm   Service manager
    *
    * @return object
    */
    public static function getGenericController($name, ServiceManager $sm)
    {
    // Prepend the current namespace unless we receive a FQCN:
    $class = (strpos($name, '\\') === false)
        ? __NAMESPACE__ . '\\' . $name : $name;
    if (!class_exists($class)) {
        throw new \Exception('Cannot construct ' . $class);
    }
    return new $class($sm->getServiceLocator());
    }

    /**
    * Construct a generic controller.
    *
    * @param string $name Method name being called
    * @param array  $args Method arguments
    *
    * @return object
    */
    public static function __callStatic($name, $args)
    {
    // Strip "get" from method name to get name of class; pass first argument
    // on assumption that it should be the ServiceManager object.
    return static::getGenericController(
        substr($name, 3), isset($args[0]) ? $args[0] : null
    );
    }

    /**
     * Construct the RecordController.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return RecordController
     */
    public static function getRecordController(ServiceManager $sm)
    {
        return new RecordController(
            $sm->getServiceLocator(),
            $sm->getServiceLocator()->get('VuFind\Config')->get('config')
        );
    }

}
