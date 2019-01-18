<?php
/**
 * Lobid connection class
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
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
namespace Fiddk\Connection;

/**
 * Wikipedia connection class
 *
 * @category VuFind
 * @package  Connection
 * @author   Chris Hallberg <challber@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
class Lobid
{

    /**
     * HTTP client
     *
     * @var \Zend\Http\Client
     */
    protected $client;

    /**
     * Log of authority gnd's already retrieved
     *
     * @var array
     */
    protected $pagesRetrieved = [];

    /**
     * Constructor
     *
     * @param \Zend\Http\Client $client HTTP client
     */
    public function __construct(\Zend\Http\Client $client)
    {
        $this->client = $client;
    }

    /**
     * This method is responsible for connecting to Lobid via the REST API
     * and pulling the JSON content for the relevant gnd.
     *
     * @param string $gnd The id of the authority to search for
     *
     * @return array
     */
    public function get($gnd)
    {
        // Don't retrieve the same gnd multiple times;
        if ($this->alreadyRetrieved($gnd)) {
            return [];
        }

        // Get information from Wikipedia API
        $uri = 'http://lobid.org/gnd/' . $gnd . '.json';
        $response = $this->client->setUri($uri)->setMethod('GET')->send();
        if ($response->isSuccess()) {
          return $this->parseJson($response->getBody());
        }
        return null;
    }

    /**
     * Check if a page has already been retrieved; if it hasn't, flag it as
     * retrieved for future reference.
     *
     * @param string $author Author being retrieved
     *
     * @return bool
     */
    protected function alreadyRetrieved($author)
    {
        if (isset($this->pagesRetrieved[$author])) {
            return true;
        }
        $this->pagesRetrieved[$author] = true;
        return false;
    }

    /**
     * Parses json output from lobid gnd
     *
     * @param string $body The retrieved JSON
     *
     * @return array
     */
    public function parseJson($body)
    {
        if ($body) {
          $json = json_decode($body,true);
          return $json;
        } else {
          return [];
      }
    }

}
