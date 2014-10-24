<?php

namespace Olcs\Data\Object\Search;

/**
 * Class Licence
 * @package Olcs\Data\Object\Search
 */
class Application extends SearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Applications';
    /**
     * @var string
     */
    protected $key = 'application';

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            ['title' => 'Application id', 'name'=> 'appId'],
            ['title' => 'Application status', 'name'=> 'appStatus'],
            ['title' => 'Date received', 'name'=> 'receivedDate'],
            ['title' => 'Licence number', 'name'=> 'licNo'],
            ['title' => 'Licence status', 'name'=> 'licStatus'],
            ['title' => 'Licence type', 'name'=> 'licType'],
            ['title' => 'Operator name', 'name'=> 'name'],
            ['title' => 'Authorisation vehicles', 'name'=> 'totAuthVehicles'],
            ['title' => 'Authorisation trailers', 'name'=> 'totAuthTrailers'],
        ];
    }
}
