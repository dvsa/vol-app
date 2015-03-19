<?php

namespace Olcs\Data\Object\Search;

/**
 * Class BusReg
 * @package Olcs\Data\Object\Search
 */
class BusReg extends InternalSearchAbstract
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
     * Contains an array of the instantiated filters classes.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Returns an array of filters for this index
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

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
