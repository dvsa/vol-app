<?php

namespace Olcs\Data\Object\Search;

/**
 * Class Vehicle
 * @package Olcs\Data\Object\Search
 */
class Vehicle extends SearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Vehicle';
    /**
     * @var string
     */
    protected $key = 'vehicle';

    /**
     * @var string
     */
    protected $searchIndices = 'vehicle_current|vehicle_removed';

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            ['title' => 'Licence number', 'name'=> 'licNo'],
            ['title' => 'Licence status', 'name'=> 'licStatusDesc'],
            ['title' => 'Operator name', 'name'=> 'orgName'],
            ['title' => 'VRM', 'name'=> 'vrm'],
            ['title' => 'Disc Number', 'name'=> 'discNo'],
            ['title' => 'Specified date', 'name'=> 'specifiedDate'],
            ['title' => 'Removed date', 'name'=> 'removalDate'],
        ];
    }
}
