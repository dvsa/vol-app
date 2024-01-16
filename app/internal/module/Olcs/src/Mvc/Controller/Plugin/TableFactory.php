<?php

namespace Olcs\Mvc\Controller\Plugin;

use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableBuilderFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TableFactory implements FactoryInterface
{
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
        $tableBuilderFactory = new TableBuilderFactory();
        $tableBuilder = $tableBuilderFactory(
            $container,
            TableBuilder::class
        );
        return new Table($tableBuilder);
    }
}
