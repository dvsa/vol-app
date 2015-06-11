<?php

namespace Olcs\Mvc\Controller\Plugin;

use Common\Service\Table\TableBuilder;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class Table extends AbstractPlugin
{
    private $tableBuilder;

    public function __construct(TableBuilder $tableBuilder)
    {
        $this->tableBuilder = $tableBuilder;
    }

    public function __invoke()
    {
        return $this;
    }

    public function buildTable($tableName, $data, $params)
    {
        $params['query'] = $this->getController()->getRequest()->getQuery();

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