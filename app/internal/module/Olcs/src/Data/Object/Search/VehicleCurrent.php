<?php

namespace Olcs\Data\Object\Search;

/**
 * Class VehicleCurrent
 * @package Olcs\Data\Object\Search
 */
class VehicleCurrent extends SearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Vehicle';
    /**
     * @var string
     */
    protected $key = 'vehicle_current';

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
