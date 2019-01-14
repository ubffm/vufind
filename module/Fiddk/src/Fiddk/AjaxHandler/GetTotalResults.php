<?php
/**
 * "Get Total Results" AJAX handler
 *
 * PHP version 7
 *
 * Copyright (C) Villanova University 2018.
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
 * @package  AJAX
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
namespace Fiddk\AjaxHandler;

use VuFind\Search\Results\PluginManager as ResultsManager;
use VuFind\Search\SearchRunner;
use VuFind\Session\Settings as SessionSettings;
use Zend\Mvc\Controller\Plugin\Params;
use Zend\Stdlib\Parameters;
use Zend\View\Renderer\RendererInterface;

/**
 * "Get Facet Data" AJAX handler
 *
 * Get hierarchical facet data for jsTree
 *
 * Parameters:
 * facetName  The facet to retrieve
 * facetSort  By default all facets are sorted by count. Two values are available
 * for alternative sorting:
 *   top = sort the top level alphabetically, rest by count
 *   all = sort all levels alphabetically
 *
 * @category VuFind
 * @package  AJAX
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
class GetTotalResults extends \VuFind\AjaxHandler\AbstractBase
{

    /**
     * Results plugin manager
     *
     * @var ResultsManager
     */
    protected $resultsManager;
    /**
     * Search runner
     *
     * @var SearchRunner
     */
    protected $searchRunner;

    /**
     * Search runner
     *
     * @var RendererInterface
     */
    protected $renderer;
    /**
     * Constructor
     *
     * @param ResultsManager    $results  Results manager
     * @param SearchRunner      $sr       Search runner
     * @param SessionSettings   $ss       Session settings
     */
    public function __construct(SessionSettings $ss, ResultsManager $rm,
      SearchRunner $sr, RendererInterface $renderer
    ) {
        $this->sessionSettings = $ss;
        $this->resultsManager = $rm;
        $this->searchRunner = $sr;
        $this->renderer = $renderer;
    }
    /**
     * Handle a request.
     *
     * @param Params $params Parameter helper from controller
     *
     * @return array [response data, HTTP status code]
     */
    public function handleRequest(Params $params)
    {
      $this->disableSessionWrites();  // avoid session write timing bug
      $searchClass = $params->fromPost('searchClassId', $params->fromQuery('searchClassId'));
      $results = $this->resultsManager->get($searchClass);
      $paramsObj = $results->getParams();
      $paramsObj->initFromRequest(new Parameters($params->fromQuery()));
      $resultTotal = $results->getResultTotal();
      //$localized = $this->renderer->plugin('localizednumber');

      return $this->formatResponse(compact('resultTotal'));

    }

}
