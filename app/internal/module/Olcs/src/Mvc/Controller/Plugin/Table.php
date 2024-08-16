<?php

namespace Olcs\Mvc\Controller\Plugin;

use Common\Service\Table\TableBuilder;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Class Table
 * @package Olcs\Mvc\Controller\Plugin
 */
class Table extends AbstractPlugin
{
    public function __construct(private TableBuilder $tableBuilder)
    {
    }

    /**
     * @return $this
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * gives us a new table builder, necessary if we've more than one table on a page
     */
    public function setTableBuilder(TableBuilder $tableBuilder)
    {
        $this->tableBuilder = $tableBuilder;

        return $this;
    }

    /**
     * @param $tableName
     * @param $data
     * @param $params
     * @return string|TableBuilder
     */
    public function buildTable($tableName, $data, $params)
    {
        $params['query'] = $this->getController()->getRequest()->getQuery();

        /**  $this->tableBuilder \Common\Service\Table\TableBuilder Traceability */
        return $this->tableBuilder->buildTable($tableName, $data, $params, false);
    }

    /**
     * @return \Laminas\Mvc\Controller\AbstractActionController
     */
    public function getController()
    {
        return parent::getController();
    }
}
