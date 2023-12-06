<?php

namespace FiddkApi\Controller;

class CorporationApiController extends \FiddkApi\Controller\SearchApiController
{
    /**
     * Search class family to use.
     *
     * @var string
     */
    protected $searchClassId = 'SolrCorporation';

    /**
     * Record route uri
     *
     * @var string
     */
    protected $recordRoute = 'corporation';

    /**
     * Search route uri
     *
     * @var string
     */
    protected $searchRoute = 'corporationsearch';

    /**
     * Descriptive label for the index managed by this controller
     *
     * @var string
     */
    protected $indexLabel = 'corporation';

    /**
     * Prefix for use in model names used by API
     *
     * @var string
     */
    protected $modelPrefix = 'corporation';

}

