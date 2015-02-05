<?php

namespace Olcs\Data\Object\Search;

/**
 * Class BusReg
 * @package Olcs\Data\Object\Search
 */
class BusReg extends SearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Bus registrations';
    /**
     * @var string
     */
    protected $key = 'bus_reg';

    /**
     * @var string
     */
    protected $searchIndices = 'bus_reg';

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            ['title' => 'Route number', 'name'=> 'routeNo'],
            ['title' => 'Route description', 'name'=> 'routeDescription']
        ];
    }
}
