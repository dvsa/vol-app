<?php

namespace Olcs\Service\Utility;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class PublicationHelperFactory
 * @package Olcs\Service\Utility
 */
class PublicationHelperFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return DateUtility
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $service = new PublicationHelper();

        /** @var \Common\Service\Data\PublicationLink $publicationLinkService */
        $publicationLinkService = $serviceLocator->get('DataServiceManager')->get('Common\Service\Data\PublicationLink');

        $service->setPublicationLinkService($publicationLinkService);

        return $service;
    }
}
