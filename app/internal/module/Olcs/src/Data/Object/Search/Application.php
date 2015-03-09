<?php

namespace Olcs\Data\Object\Search;

/**
 * Class Licence
 * @package Olcs\Data\Object\Search
 */
class Application extends SearchAbstract
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
            ['title' => 'Application id', 'name'=> 'appId'],
            ['title' => 'Application status', 'name'=> 'appStatusDesc'],
            ['title' => 'Date received', 'name'=> 'receivedDate'],
            ['title' => 'Licence number', 'name'=> 'licNo'],
            ['title' => 'Licence status', 'name'=> 'licStatusDesc'],
            ['title' => 'Licence type', 'name'=> 'licTypeDesc'],
            ['title' => 'Operator name', 'name'=> 'orgName'],
            ['title' => 'Authorisation vehicles', 'name'=> 'totAuthVehicles'],
            ['title' => 'Authorisation trailers', 'name'=> 'totAuthTrailers'],
        ];
    }
}
