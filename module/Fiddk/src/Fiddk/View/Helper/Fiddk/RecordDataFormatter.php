<?php
/**
 * Record driver data formatting view helper
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2016.
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
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:architecture:record_data_formatter
 * Wiki
 */
namespace Fiddk\View\Helper\Fiddk;
use VuFind\RecordDriver\AbstractBase as RecordDriver;
use Zend\View\Helper\AbstractHelper;

/**
 * Record driver data formatting view helper
 *
 * @category VuFind
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:architecture:record_data_formatter
 * Wiki
 */
class RecordDataFormatter extends \VuFind\View\Helper\Root\RecordDataFormatter
{
    /**
     * Create formatted key/value data based on a record driver and field spec.
     *
     * @param RecordDriver $driver Record driver object.
     * @param array        $spec   Formatting specification
     *
     * @return array
     */
    public function getData(RecordDriver $driver, array $spec)
    {
        $result = [];

        // Sort the spec into order by position:
        uasort($spec, [$this, 'specSortCallback']);
        $driver->getFullRecord();
        $driver->getEDMClasses();

        // Apply the spec:
        foreach ($spec as $field => $current) {
            // Extract the relevant data from the driver.
            $data = $this->extractData($driver, $current);
            $allowZero = isset($current['allowZero']) ? $current['allowZero'] : true;
            if (!empty($data) || ($allowZero && ($data === 0 || $data === '0'))) {
                // Determine the rendering method to use with the second element
                // of the current spec.
                $renderMethod = empty($current['renderType'])
                    ? 'renderSimple' : 'render' . $current['renderType'];

                // Add the rendered data to the return value if it is non-empty:
                if (is_callable([$this, $renderMethod])) {
                    $text = $this->$renderMethod($driver, $data, $current);
                    if (!$text && (!$allowZero || ($text !== 0 && $text !== '0'))) {
                        continue;
                    }
                    // Allow dynamic label override:
                    if (isset($current['labelFunction'])
                        && is_callable($current['labelFunction'])
                    ) {
                        $field = call_user_func($current['labelFunction'], $data);
                    }
                    $context = isset($current['context']) ? $current['context'] : [];
                    $result[$field] = [
                        'value' => $text,
                        'context' => $context
                    ];
                }
            }
        }
        return $result;
    }
}
