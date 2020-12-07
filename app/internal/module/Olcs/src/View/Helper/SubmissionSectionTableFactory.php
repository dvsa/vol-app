<?php

namespace Olcs\View\Helper;

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
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $tableBuilder = $serviceLocator->getServiceLocator()->get('Table');
        $service = new SubmissionSectionTable();
        $service->setTableBuilder($tableBuilder);

        return $service;
    }
}
