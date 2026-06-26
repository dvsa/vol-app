<?php

namespace Common\Service\Table;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Table Factory
 * Creates an instance of TableBuilder and passes in the application config
 *
 * @deprecated See: olcs-common/Common/config/module.config.php, line: 273
 */
class TableFactory implements FactoryInterface
{
    private ContainerInterface $serviceLocator;

    /**
     * Create the table factory service
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->serviceLocator = $container;
        return $this;
    }

    /**
     * Get an instance of table builder
     *
     * @return \Common\Service\Table\TableBuilder
     */
    public function getTableBuilder()
    {
        $tableBuilderFactory = new TableBuilderFactory();
        return $tableBuilderFactory($this->serviceLocator, TableBuilder::class);
    }


    public function prepareTable($name, array $data = [], array $params = [])
    {
        return $this->getTableBuilder()->prepareTable($name, $data, $params);
    }

    /**
     * Wrap the build table method
     *
     * @param string $name
     * @param array $data
     * @param array $params
     * @param boolean $render
     */
    public function buildTable($name, $data = [], $params = [], $render = true)
    {
        $table = $this->getTableBuilder();
        return $table->buildTable($name, $data, $params, $render);
    }
}
