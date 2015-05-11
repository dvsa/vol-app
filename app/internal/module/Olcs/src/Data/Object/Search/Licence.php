<?php
namespace Olcs\Data\Object\Search;

use Olcs\Data\Object\Search\Aggregations\Terms as Filter;

/**
 * Class Licence
 * @package Olcs\Data\Object\Search
 */
class Licence extends InternalSearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Licence';
    /**
     * @var string
     */
    protected $key = 'licence';

    /**
     * @var string
     */
    protected $searchIndices = 'licence';

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
                new Filter\TrafficArea(),
                new Filter\EntityType(),
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
            ['title' => 'Trading name', 'name'=> 'tradingName'],
            ['title' => 'Entity type', 'name'=> 'orgTypeDesc'],
            ['title' => 'Licence type', 'name'=> 'licTypeDesc'],
            [
                'title' => 'Cases',
                'name'=> 'caseCount',
                'formatter' => function ($data) {
                    return '<a href="/licence/' . $data['licId'] . '/cases">' . $data['caseCount'] . '</a>';
                }
            ]
        ];
    }
}
