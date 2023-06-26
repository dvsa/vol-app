<?php

namespace Olcs\Mvc\Controller\Plugin;

use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableBuilderFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class TableFactory
 * @package Olcs\Mvc\Controller\Plugin
 */
class TableFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator): Table
    {
        return $this->__invoke($serviceLocator, Table::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Table
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Table
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        $tableBuilderFactory = new TableBuilderFactory();
        $tableBuilder = $tableBuilderFactory(
            $container,
            TableBuilder::class
        );
        return new Table($tableBuilder);
    }
}
