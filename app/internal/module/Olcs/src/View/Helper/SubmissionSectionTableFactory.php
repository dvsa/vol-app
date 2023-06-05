<?php

namespace Olcs\View\Helper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class SubmissionSectionsTableFactory
 * @package Olcs\View\Helper
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class SubmissionSectionTableFactory implements FactoryInterface
{
    /**
     * Create SubmissionSectionTable with injected table builder
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return SubmissionSectionTable
     */
    public function createService(ServiceLocatorInterface $serviceLocator): SubmissionSectionTable
    {
        return $this->__invoke($serviceLocator, SubmissionSectionTable::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SubmissionSectionTable
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SubmissionSectionTable
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        $tableBuilder = $container->get('Table');
        $service = new SubmissionSectionTable();
        $service->setTableBuilder($tableBuilder);
        return $service;
    }
}
