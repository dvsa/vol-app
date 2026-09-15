<?php

namespace CommonTest\Common\Service\Data\Search\Asset;

use Common\Data\Object\Search\SearchAbstract;

/**
 * Class Licence
 * @package Olcs\Data\Object\Search
 */
class SearchType extends SearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'SearchType';

    /**
     * @var string
     */
    protected $key = 'searchtype';

    /**
     * @var string
     */
    protected $searchIndices = 'searchtype';

    /**
     * Contains an array of the instantiated filters classes.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * @return array
     */
    #[\Override]
    public function getColumns()
    {
        return [
            ['title' => 'Search Type', 'name' => 'searchtypeId'],
        ];
    }
}
