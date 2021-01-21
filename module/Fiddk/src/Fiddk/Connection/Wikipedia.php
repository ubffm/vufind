<?php
/**
 * Wikipedia connection class
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
 * @package  Connection
 * @author   Chris Hallberg <challber@villanova.edu>
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
namespace Fiddk\Connection;

use VuFind\I18n\Translator\TranslatorAwareInterface;

/**
 * Wikipedia connection class
 *
 * @category VuFind
 * @package  Connection
 * @author   Chris Hallberg <challber@villanova.edu>
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
class Wikipedia extends \VuFind\Connection\Wikipedia implements TranslatorAwareInterface
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;

    /**
     * This method is responsible for connecting to Wikimedia API
     * and gets JSON content from it
     *
     * @param string $request The request to the API
     *
     * @return array
     */
    public function getJSON($request)
    {
        // Don't retrieve the same request answer multiple times;
        if ($this->alreadyRetrieved($request)) {
            return [];
        }

        // Get information from Wikimedia API
        $uri = 'http://' . $this->lang . '.wikipedia.org/w/api.php?action=query' . $request . '&format=json';
        $response = $this->client->setUri($uri)->setMethod('GET')->send();
        if ($response->isSuccess() && $body = $response->getBody()) {
          $json = json_decode($body,true);
          return $json["query"]["pages"];
        }
        return null;
    }

}
