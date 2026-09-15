<?php

namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms\BusRegStatus;
use Common\Data\Object\Search\Aggregations\Terms\TrafficArea;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\SearchAddressOperatorName;

/**
 * Class BusReg
 * @package Common\Data\Object\Search
 */
class BusReg extends InternalSearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Bus registrations';

    /**
     * @var string
     */
    protected $key = 'bus_reg';

    /**
     * @var string
     */
    protected $searchIndices = 'busreg';

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
    #[\Override]
    public function getFilters()
    {
        if (empty($this->filters)) {
            $this->filters = [
                new TrafficArea(),
                new BusRegStatus(),
            ];
        }

        return $this->filters;
    }

    /**
     * @return array
     */
    #[\Override]
    public function getColumns()
    {
        return [
            [
                'title' => 'Registration number',
                'name' => 'regNo',
                'formatter' => static fn($data) => '<a class="govuk-link" href="/licence/'
                . $data['licId'] . '/bus/' . $data['busregId']
                . '/details">' . $data['regNo'] . '</a>'
            ],
            [
                'title' => 'Operator name',
                'name' => 'orgName',
                'formatter' => SearchAddressOperatorName::class
            ],
            ['title' => 'Variation number', 'name' => 'variationNo'],
            ['title' => 'Status', 'name' => 'busRegStatus'],
            [
                'title' => 'Date first registered / cancelled',
                'formatter' => Date::class,
                'name' => 'date_1stReg'
            ],
            ['title' => 'Service number', 'name' => 'serviceNo'],
            ['title' => 'Start point', 'name' => 'startPoint'],
            ['title' => 'Finish point', 'name' => 'finishPoint']
        ];
    }
}
