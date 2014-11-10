<?php

namespace Olcs\Data\Object\Search;

/**
 * Class Licence
 * @package Olcs\Data\Object\Search
 */
class Licence extends SearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Licence';
    /**
     * @var string
     */
    protected $key = 'licence';

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            ['title' => 'Licence number', 'name'=> 'licNo'],
            ['title' => 'Licence status', 'name'=> 'licStatusDesc'],
            ['title' => 'Operator name', 'name'=> 'orgName'],
            ['title' => 'Trading name', 'name'=> 'tradingName'],
            ['title' => 'Entity type', 'name'=> 'orgTypeDesc'],
            ['title' => 'Licence type', 'name'=> 'licTypeDesc'],
            ['title' => 'Cases', 'name'=> 'caseCount'],
        ];
    }
}
