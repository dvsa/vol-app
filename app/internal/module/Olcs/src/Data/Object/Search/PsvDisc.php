<?php

namespace Olcs\Data\Object\Search;

use Olcs\Data\Object\Search\Aggregations\Terms as Filter;

/**
 * Class PsvDisc
 * @package Olcs\Data\Object\Search
 */
class PsvDisc extends InternalSearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Psv Disc';
    /**
     * @var string
     */
    protected $key = 'psv_disc';

    /**
     * @var string
     */
    protected $searchIndices = 'psv_disc';

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
            //['title' => 'Licence number', 'name'=> 'licNo'],
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
            ['title' => 'VRM', 'name'=> ''],
            ['title' => 'Disc Number', 'name'=> 'discNo'],
            ['title' => 'Specified date', 'name'=> ''],
            ['title' => 'Removed date', 'name'=> ''],
        ];
    }
}
