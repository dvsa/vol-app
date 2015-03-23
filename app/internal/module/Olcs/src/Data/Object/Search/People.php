<?php

namespace Olcs\Data\Object\Search;

/**
 * Class People
 * @package Olcs\Data\Object\Search
 */
class People extends InternalSearchAbstract
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
            ['title' => 'Forename', 'name'=> 'foreName'],
            ['title' => 'Family name', 'name'=> 'familyName'],
            ['title' => 'DOB', 'name'=> 'dob']
        ];
    }
}
