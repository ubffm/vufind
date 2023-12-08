<?php

/**
 * VuFind Bootstrapper
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
 * @package  Bootstrap
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */

namespace Fiddk;

use \Fiddk\Connection\Wagtail;

/**
 * VuFind Bootstrapper
 *
 * @category VuFind
 * @package  Bootstrap
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class Bootstrapper extends \VuFind\Bootstrapper implements 
\VuFindHttp\HttpServiceAwareInterface
{
    use \VuFindHttp\HttpServiceAwareTrait;

    /**
     * HTTP client
     *
     * @var \Laminas\Http\Client
     */
    protected $client;

    /**
     * Wagtail client
     *
     * @var Wagtail
     */
    protected $wagtail;

    /**
     * HTTP Service
     *
     */
    protected $httpService;

    /**
     * Service manager
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Current MVC event
     *
     * @var MvcEvent
     */
    protected $event;

    /**
     * Constructor
     *
     * @param MvcEvent $event Laminas MVC Event object
     */
    public function __construct(\Laminas\Mvc\MvcEvent $event)
    {
        parent::__construct($event);
        $this->setHttpService($this->container->get(\VuFindHttp\HttpService::class));
    }

    /**
     * Set up the initial view model.
     *
     * @return void
     */
    protected function initViewModel(): void
    {
        parent::initViewModel();
        $viewModel = $this->container->get('HttpViewManager')->getViewModel();
        $this->client = $this->httpService->createClient();
        $this->wagtail = new Wagtail($this->client);
        $viewModel->setVariable('navigation', $this->wagtail->getNav());
        
    }
}