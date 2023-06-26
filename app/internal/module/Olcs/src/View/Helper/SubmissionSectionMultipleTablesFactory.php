<?php

namespace Olcs\View\Helper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

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
    public function createService(ServiceLocatorInterface $serviceLocator): SubmissionSectionMultipleTables
    {
        return $this->__invoke($serviceLocator, SubmissionSectionMultipleTables::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SubmissionSectionMultipleTables
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SubmissionSectionMultipleTables
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        $translator = $container->get('Translator');
        $service = new SubmissionSectionMultipleTables();
        $service->setTranslator($translator);
        return $service;
    }
}
