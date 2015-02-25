<?php

namespace Olcs\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SubmissionSectionMultipleTablesFactory
 * @package Olcs\View\Helper
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class SubmissionSectionMultipleTablesFactory implements FactoryInterface
{
    /**
     * Create SubmissionSectionMultipleTables service with injected translator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return SubmissionSectionMultipleTables
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $translator = $serviceLocator->getServiceLocator()->get('Translator');
        $service = new SubmissionSectionMultipleTables();
        $service->setTranslator($translator);

        return $service;
    }
}
