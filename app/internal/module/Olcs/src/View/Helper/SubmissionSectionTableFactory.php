<?php

namespace Olcs\View\Helper;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SubmissionSectionTableFactory implements FactoryInterface
{
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
        $tableBuilder = $container->get('Table');
        $service = new SubmissionSectionTable();
        $service->setTableBuilder($tableBuilder);
        return $service;
    }
}
