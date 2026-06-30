<?php

namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms as Filter;
use Common\Data\Object\Search\Aggregations\DateRange;
use Common\Service\Table\Formatter\Date;

/**
 * Class Publication
 * @package Common\Data\Object\Search
 */
class Publication extends InternalSearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Publication';

    /**
     * @var string
     */
    protected $key = 'publication';

    /**
     * @var string
     */
    protected $searchIndices = 'publication';

    /**
     * Contains an array of the instantiated Date Ranges classes.
     *
     * @var array
     */
    protected $dateRanges = [];

    /**
     * Contains an array of the instantiated filters classes.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Contains an array of required results order
     *
     * @var array
     */
    protected $orderOptions = [
        [
            'field' => 'pub_date',
            'field_label' => 'Most recent publication',
            'order' => 'desc'
        ],
        [
            'field' => 'pub_date',
            'field_label' => 'Oldest publication',
            'order' => 'asc'
        ],
    ];

    /**
     * Returns an array of date ranges for this index
     *
     * @return array
     */
    #[\Override]
    public function getDateRanges()
    {
        if (empty($this->dateRanges)) {
            $this->dateRanges = [
                new DateRange\PublishedDateFrom(),
                new DateRange\PublishedDateTo(),
            ];
        }

        return $this->dateRanges;
    }

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
                new Filter\TrafficArea(),
                new Filter\PublicationType(),
                new Filter\DocumentStatus(),
                new Filter\PublicationSection()
            ];
        }

        return $this->filters;
    }

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
                    'default' => 25,
                    'options' => [10, 25, 50]
                ]
            ],
            'layout' => 'traffic-commissioner-publication',
            'show-status' => true
        ];
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
                'title' => 'Publication No',
                'name' => 'pubNo'
            ],
            [
                'title' => 'Traffic area',
                'name' => 'trafficArea'
            ],
            [
                'title' => 'Publication type',
                'name' => 'pubType'

            ],
            [
                'title' => 'Publication status',
                'name' => 'pubStatusDesc'
            ],
            [
                'title' => 'Close date',
                'formatter' => Date::class,
                'name' => 'pubDate'
            ],
            [
                'title' => 'Publication section',
                'name' => 'pubSecDesc'
            ],
            [
                'title' => 'Publication details',
                'name' => 'text1'
            ]
        ];
    }
}
