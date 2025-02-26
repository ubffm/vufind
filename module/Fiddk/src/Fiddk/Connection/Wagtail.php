<?php
/**
 * Wagtail connection class
 *
 * PHP version 8
 *
 * Copyright (C) Frankfurt University Library 2023.
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
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
namespace Fiddk\Connection;

/**
 * Wagtail connection class
 *
 * @category VuFind
 * @package  Connection
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
class Wagtail
{
    /**
     * HTTP client
     *
     * @var \Laminas\Http\Client
     */
    protected $client;

    /**
     * Log of pages already retrieved
     *
     * @var array
     */
    protected $pagesRetrieved = [];

    /**
     * Constructor
     *
     * @param \Laminas\Http\Client $client HTTP client
     */
    public function __construct(\Laminas\Http\Client $client)
    {
        $this->client = $client;
    }

    /**
     * This method is responsible for connecting to Lobid via the REST API
     * and pulling the JSON content for the relevant gnd.
     *
     *TODO: cache this
     * @return array
     */
    public function getNav()
    {
        // Don't retrieve multiple times;
        if ($this->alreadyRetrieved()) {
            return [];
        }
        
        //$api = 'http://127.0.0.1:8000/de/api/v2/pages/';
        //$uri = $api . '?type=regular.RegularIndexPage&fields=depth';
        // Get information from API
        //$response = $this->client->setUri($uri)->setMethod('GET')->send();
        //if ($response->isSuccess()) {
        //    return $this->parseJson($response->getBody(),$api);
        //}
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
    protected function alreadyRetrieved()
    {
        if (isset($this->pagesRetrieved["wagtail"])) {
            return true;
        }
        $this->pagesRetrieved["wagtail"] = true;
        return false;
    }

    /**
     * Parses json output from wagtail api
     *
     * @param string $body The retrieved JSON
     *
     * @return array
     */
    public function parseJson($body,$api)    
    { // make json with key lang and tree struct
        if ($body) {
            $res = ['de'=>[], 'en'=>[]];
            $json = json_decode($body, true);
            foreach($json["items"] as $item) {
                if ($item["depth"] == 3) {
                    $id = $item["id"];
                    $lang = $item["meta"]["locale"];
                    $children_uri = $api . '?child_of=' . $id . '&show_in_menus=true';
                    $children=[];
                    $response = $this->client->setUri($children_uri)->setMethod('GET')->send();
                    if ($response->isSuccess()) {
                        $json_children = json_decode($response->getBody(), true);
                        foreach($json_children["items"] as $child_item) {
                            // no need for language check here
                            $children[] = ['title' => $child_item["title"], 'url' => $child_item["meta"]["html_url"]];
                        }
                    }
                    $res[$lang][$id] = ['id' => $id,'title' => $item["title"], 'url' => $item["meta"]["html_url"], 'children' => $children];
                }
            }
            return $res;
        } else {
            return [];
        }
    }
}
