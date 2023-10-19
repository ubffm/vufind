<?php

namespace FiddkApi\Controller;

class PersonApiController extends \FiddkApi\Controller\SearchApiController
{
    /**
     * Search class family to use.
     *
     * @var string
     */
    protected $searchClassId = 'SolrPerson';

    /**
     * Record route uri
     *
     * @var string
     */
    protected $recordRoute = 'person';

    /**
     * Search route uri
     *
     * @var string
     */
    protected $searchRoute = 'personsearch';

    /**
     * Descriptive label for the index managed by this controller
     *
     * @var string
     */
    protected $indexLabel = 'person';

    /**
     * Prefix for use in model names used by API
     *
     * @var string
     */
    protected $modelPrefix = 'person';

}

