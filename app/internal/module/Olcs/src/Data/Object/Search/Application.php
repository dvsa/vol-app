<?php

namespace Olcs\Data\Object\Search;

/**
 * Class Licence
 * @package Olcs\Data\Object\Search
 */
class Application extends InternalSearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Applications';
    /**
     * @var string
     */
    protected $key = 'application';

    /**
     * @var string
     */
    protected $searchIndices = 'application';

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
                new Filter\LicenceType(),
                new Filter\LicenceStatus(),
                new Filter\ApplicationStatus(),
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
                'title' => 'Application id',
                'name'=> 'appId',
                'formatter' => function ($data) {
                    return '<a href="/application/' . $data['appId'] . '">' . $data['appId'] . '</a>';
                }
            ],
            ['title' => 'Application status', 'name'=> 'appStatusDesc'],
            ['title' => 'Date received', 'name'=> 'receivedDate'],
            [
                'title' => 'Licence number',
                'name'=> 'licNo',
                'formatter' => function ($data) {
                    return '<a href="/licence/' . $data['licId'] . '">' . $data['licNo'] . '</a>';
                }
            ],
            ['title' => 'Licence status', 'name'=> 'licStatusDesc'],
            ['title' => 'Licence type', 'name'=> 'licTypeDesc'],
            [
                'title' => 'Operator name',
                'name'=> 'orgName',
                'formatter' => function ($data) {
                    return '<a href="/operator/' . $data['orgId'] . '">' . $data['orgName'] . '</a>';
                }
            ],
            ['title' => 'Authorisation vehicles', 'name'=> 'totAuthVehicles'],
            ['title' => 'Authorisation trailers', 'name'=> 'totAuthTrailers'],
        ];
    }
}
