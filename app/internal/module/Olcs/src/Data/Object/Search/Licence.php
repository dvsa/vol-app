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
            ['title' => 'Licence status', 'name'=> 'licStatus'],
            ['title' => 'Operator name', 'name'=> 'opName'],
            ['title' => 'Trading name', 'name'=> 'tradName'],
            ['title' => 'Entity type', 'name'=> 'entityType'],
            ['title' => 'Licence type', 'name'=> 'licType'],
            ['title' => 'Cases', 'name'=> 'cases'],
        ];
    }
}
