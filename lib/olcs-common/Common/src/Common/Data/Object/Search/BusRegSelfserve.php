<?php

namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms\BusRegStatus;
use Common\Data\Object\Search\Aggregations\Terms\TrafficArea;
use Common\Service\Table\Formatter\SearchBusRegSelfserve;
use Common\Util\Escape;

/**
 * Class BusReg
 * @package Common\Data\Object\Search
 */
class BusRegSelfserve extends InternalSearchAbstract
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
     * Get settings
     *
     * @return array
     */
    #[\Override]
    public function getSettings()
    {
        return [
            'paginate' => [
                'limit' => [
                    'options' => [10, 25, 50, 100]
                ]
            ],
            'layout' => 'headless'
        ];
    }

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
        if (count($this->filters) === 0) {
            $this->filters = [
                new TrafficArea(),
                new BusRegStatus(),
            ];
        }

        return $this->filters;
    }

    /**
     * Get columns
     *
     * @return array
     */
    #[\Override]
    public function getColumns()
    {
        return [
            [
                'title' => 'Registration number',
                'name' => 'regNo',
                'formatter' => SearchBusRegSelfserve::class
            ],
            [
                'title' => 'Operator name',
                'name' => 'orgName',
                'formatter' => static fn($data) => Escape::html($data['orgName']),
            ],
            ['title' => 'Service number', 'name' => 'serviceNo'],
            ['title' => 'Start point', 'name' => 'startPoint'],
            ['title' => 'Finish point', 'name' => 'finishPoint'],
        ];
    }
}
