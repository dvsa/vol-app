<?php

namespace Olcs\Data\Object\Search;

/**
 * Class People
 * @package Olcs\Data\Object\Search
 */
class People extends SearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'People';

    /**
     * @var string
     */
    protected $key = 'people';

    /**
     * @var string
     */
    protected $searchIndices = 'people';

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            ['title' => 'Forename', 'name'=> 'foreName'],
            ['title' => 'Family name', 'name'=> 'familyName'],
            ['title' => 'DOB', 'name'=> 'dob']
        ];
    }
}
