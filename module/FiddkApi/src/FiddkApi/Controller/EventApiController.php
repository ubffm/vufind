<?php

namespace FiddkApi\Controller;

class EventApiController extends \FiddkApi\Controller\SearchApiController
{
    /**
     * Search class family to use.
     *
     * @var string
     */
    protected $searchClassId = 'SolrEvent';

    /**
     * Record route uri
     *
     * @var string
     */
    protected $recordRoute = 'event';

    /**
     * Search route uri
     *
     * @var string
     */
    protected $searchRoute = 'eventsearch';

    /**
     * Descriptive label for the index managed by this controller
     *
     * @var string
     */
    protected $indexLabel = 'event';

    /**
     * Prefix for use in model names used by API
     *
     * @var string
     */
    protected $modelPrefix = 'event';

}

