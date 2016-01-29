<?php

namespace Olcs\Service\Utility;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class DateUtlityFactory
 * @package Olcs\Service\Utilityt
 */
class DateUtilityFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return DateUtility
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $service = new DateUtility();

        /** @var \Common\Util\DateTimeProcessor $dateTimeProcessor */
        $dateTimeProcessor = $serviceLocator->get('Common\Util\DateTimeProcessor');

        $service->setDateTimeProcessor($dateTimeProcessor);

        return $service;
    }
}
