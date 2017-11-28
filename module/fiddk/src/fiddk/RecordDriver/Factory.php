<?php
/**
 * Userdefined Record Driver Factory Class
 *
 * PHP version 5
 *
 * Record Driver Factory for SolrEdm based on the model of the Factory Class
 * written by Demian Katz.
 *
 * @category VuFind2
 * @package  RecordDrivers
 * @author   Julia Beck <j.beck@ub.uni-frankfurt.de>
 */
namespace fiddk\RecordDriver;
use Zend\ServiceManager\ServiceManager;

class Factory
{
    public static function getSolrEdm(ServiceManager $sm)
    {
        $driver = new SolrEdm(
            $sm->getServiceLocator()->get('VuFind\Config')->get('config'),
            null,
            $sm->getServiceLocator()->get('VuFind\Config')->get('searches'),
            $sm->getServiceLocator()->get('VuFind\RecordLoader')
        );
        $driver->attachSearchService($sm->getServiceLocator()->get('VuFind\Search'));
        return $driver;
    }

    public static function getSolrAuth(ServiceManager $sm)
    {
        return new SolrAuth(
            $sm->getServiceLocator()->get('VuFind\Config')->get('config'),
            null,
            $sm->getServiceLocator()->get('VuFind\Config')->get('searches'),
            $sm->getServiceLocator()->get('VuFind\RecordLoader')
        );
    }
}
