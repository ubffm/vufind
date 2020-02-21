<?php
/**
 * AuthorInfo Recommendations Module
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
 * @package  Recommendations
 * @author   Chris Hallberg <challber@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:recommendation_modules Wiki
 */
namespace Fiddk\Recommend;

use Fiddk\Connection\Lobid;
use VuFindSearch\Query\Query;

/**
 * AuthorInfo Recommendations Module
 *
 * This class gathers information from the Wikipedia API and publishes the results
 * to a module at the top of an author's results page
 *
 * @category VuFind
 * @package  Recommendations
 * @author   Chris Hallberg <challber@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:recommendation_modules Wiki
 * @view     AuthorInfoFacets.phtml
 */
class AuthInfo implements \VuFind\Recommend\RecommendInterface
{

    /**
     * HTTP client
     *
     * @var \Zend\Http\Client
     */
    protected $client;

    /**
     * Lobid client
     *
     * @var Lobid
     */
    protected $lobid;

    /**
     * Saved search results
     *
     * @var \VuFind\Search\Base\Results
     */
    protected $searchObject;

    /**
     * Results plugin manager
     *
     * @var \VuFind\Search\Results\PluginManager
     */
    protected $resultsManager;

    /**
     * Should we use VIAF for authorized names?
     *
     * @var bool
     */
    protected $useViaf = false;

    /**
     * Sources of author data that may be used (comma-delimited string; currently
     * only 'wikipedia' is supported).
     *
     * @var string
     */
    protected $sources;

    protected $dates = ['de' => ['01' => 'Januar', '02' => 'Februar', '03' => 'März',
      '04' => 'April', '05' => 'Mai', '06' => 'Juni', '07' => 'Juli', '08' => 'August',
      '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Dezember'],
      'en' => ['01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
      '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
      '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December']];

    /**
     * Constructor
     *
     * @param \VuFind\Search\Results\PluginManager $results Results plugin manager
     * @param \Zend\Http\Client                    $client  HTTP client
     * @param string                               $sources Source identifiers
     * (currently, only 'wikipedia' is supported)
     */
    public function __construct(\VuFind\Search\Results\PluginManager $results,
        \Zend\Http\Client $client
    ) {
        $this->resultsManager = $results;
        $this->client = $client;
        $this->lobid = new Lobid($client);
        $this->sources = 'lobid';
    }

    /**
     * Store the configuration of the recommendation module.
     *
     * @param string $settings Settings from searches.ini.
     *
     * @return void
     */
    public function setConfig($settings)
    {
        $parts = explode(':', $settings);
        if (isset($parts[0]) && !empty($parts[0])
            && strtolower(trim($parts[0])) !== 'false'
        ) {
            $this->useViaf = true;
        }
    }

    /**
     * Called at the end of the Search Params objects' initFromRequest() method.
     * This method is responsible for setting search parameters needed by the
     * recommendation module and for reading any existing search parameters that may
     * be needed.
     *
     * @param \VuFind\Search\Base\Params $params  Search parameter object
     * @param \Zend\StdLib\Parameters    $request Parameter object representing user
     * request.
     *
     * @return void
     */
    public function init($params, $request)
    {
        // No action needed here.
    }

    /**
     * Called after the Search Results object has performed its main search.  This
     * may be used to extract necessary information from the Search Results object
     * or to perform completely unrelated processing.
     *
     * @param \VuFind\Search\Base\Results $results Search results object
     *
     * @return void
     */
    public function process($results)
    {
        $this->searchObject = $results;
    }

    /**
     * Checks if it is a gnd or a normal authority search and returns
     * either info from lobid gnd to the view or info from the index
     *
     * @return array info
     */
    public function getAuthInfo()
    {
        $gnd = $this->getGnd();
        return !empty($gnd)
            ? $this->lobid->get($gnd) : null;
    }

    /**
     * Formats display date
     *
     * @return array info
     */
    public function formatDisplayDate($date, $lang)
    {
        preg_match('/(\d{4})-(\d{2})-(\d{2})/', $date, $matches, PREG_OFFSET_CAPTURE);
        if (!empty($matches) && isset($matches[1][0]) && isset($matches[2][0])
          && isset($matches[3][0])) {
          $str = '';
          if ($matches[3][0][0] == '0') {
            $str .= $matches[3][0][1];
          } else {
            $str .= $matches[3][0];
          }
          if ($lang == 'de') {
            $str .= '. ' . $this->dates['de'][$matches[2][0]];
          } else {
            $str .= ' ' . $this->dates['en'][$matches[2][0]];
          }
          $str .= ' ' . $matches[1][0];
          return $str;
        } else {
          return $date;
        }
    }

    /**
     * Takes the search term and extracts the gnd from it
     *
     * @return string
     */
    protected function getGnd()
    {
        $search = $this->searchObject->getParams()->getQuery();
        //var_dump($search);
        if ($search instanceof Query) {
          $gnd = substr($search->getString(),4);
          return $gnd;
        } else {
          return '';
        }
    }
}
