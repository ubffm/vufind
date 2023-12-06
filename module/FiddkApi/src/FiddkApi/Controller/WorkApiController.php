<?php

namespace FiddkApi\Controller;

class WorkApiController extends \FiddkApi\Controller\SearchApiController
{
    /**
     * Search class family to use.
     *
     * @var string
     */
    protected $searchClassId = 'SolrWork';

    /**
     * Record route uri
     *
     * @var string
     */
    protected $recordRoute = 'work';

    /**
     * Search route uri
     *
     * @var string
     */
    protected $searchRoute = 'worksearch';

    /**
     * Descriptive label for the index managed by this controller
     *
     * @var string
     */
    protected $indexLabel = 'work';

    /**
     * Prefix for use in model names used by API
     *
     * @var string
     */
    protected $modelPrefix = 'work';

}

