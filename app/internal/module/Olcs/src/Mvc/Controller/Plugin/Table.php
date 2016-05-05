<?php

namespace Olcs\Mvc\Controller\Plugin;

use Common\Service\Table\TableBuilder;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Class Table
 * @package Olcs\Mvc\Controller\Plugin
 */
class Table extends AbstractPlugin
{
    /**
     * @var TableBuilder
     */
    private $tableBuilder;

    /**
     * @param TableBuilder $tableBuilder
     */
    public function __construct(TableBuilder $tableBuilder)
    {
        $this->tableBuilder = $tableBuilder;
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
     * @return \Zend\Mvc\Controller\AbstractActionController
     */
    public function getController()
    {
        return parent::getController();
    }
}
