<?php

namespace Olcs\Data\Object\Search;

/**
 * Class Address
 * @package Olcs\Data\Object\Search
 */
class Address extends SearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Address';

    /**
     * @var string
     */
    protected $key = 'address';

    /**
     * @var string
     */
    protected $searchIndices = 'address';

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
            ['title' => 'Address line 1', 'name'=> 'addressLine1'],
            ['title' => 'Address line 2', 'name'=> 'addressLine2'],
            ['title' => 'Street', 'name'=> 'street'],
            ['title' => 'Town', 'name'=> 'town']
        ];
    }
}
