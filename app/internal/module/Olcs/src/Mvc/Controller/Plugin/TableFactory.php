<?php

namespace Olcs\Mvc\Controller\Plugin;

use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableBuilderFactory;
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
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $tableBuilderFactory = new TableBuilderFactory();

        $tableBuilder = $tableBuilderFactory(
            $serviceLocator->getServiceLocator(),
            TableBuilder::class
        );

        return new Table($tableBuilder);
    }
}
