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
