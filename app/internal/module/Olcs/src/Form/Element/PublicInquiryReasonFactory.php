<?php

namespace Olcs\Form\Element;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class PublicInquiryReasonFactory
 * @package Olcs\Form\Element
 */
class PublicInquiryReasonFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $formElementManager
     * @return PublicInquiryReason
     */
    public function createService(ServiceLocatorInterface $formElementManager)
    {
        /** @var \Zend\Form\FormElementManager $formElementManager */
        $serviceLocator = $formElementManager->getServiceLocator();

        $service = new PublicInquiryReason();
        $service->setServiceLocator($serviceLocator);
        $service->setLicenceService($serviceLocator->get('Olcs\Service\Data\Licence'));

        return $service;
    }
}
