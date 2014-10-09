<?php

namespace Olcs\Service\Data;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SubmissionFactory
 * @package Olcs\Service\Data
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class SubmissionFactory implements FactoryInterface
{
    /**
     * Create Submission service with injected ref data service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Submission
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $refDataService = $serviceLocator->get('Common\Service\Data\RefData');
        $service = new Submission();
        $service->setRefDataService($refDataService);

        return $service;
    }
}
