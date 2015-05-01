<?php

namespace Olcs\Data\Object\Search;

/**
 * Class Vehicle
 * @package Olcs\Data\Object\Search
 */
class Vehicle extends InternalSearchAbstract
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
        if (empty($this->filters)) {

            $this->filters = [
                new Filter\LicenceStatus(),
            ];
        }

        return $this->filters;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            [
                'title' => 'Licence number',
                'name'=> 'licNo',
                'formatter' => function ($data) {
                    return '<a href="/licence/' . $data['licId'] . '">' . $data['licNo'] . '</a>';
                }
            ],
            ['title' => 'Licence status', 'name'=> 'licStatusDesc'],
            [
                'title' => 'Operator name',
                'name'=> 'orgName',
                'formatter' => function ($data) {
                    return '<a href="/operator/' . $data['orgId'] . '">' . $data['orgName'] . '</a>';
                }
            ],
            ['title' => 'VRM', 'name'=> 'vrm'],
            ['title' => 'Disc Number', 'name'=> 'discNo'],
            ['title' => 'Specified date', 'name'=> 'specifiedDate'],
            ['title' => 'Removed date', 'name'=> 'removalDate'],
        ];
    }
}
