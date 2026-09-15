<?php

namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms as Filter;
use Common\Data\Object\Search\Aggregations\DateRange;

/**
 * Class Publications
 * Used by Selfserve publication search
 *
 * @package Common\Data\Object\Search
 */
class PublicationSelfserve extends InternalSearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Traffic Commissioner Publication';

    /**
     * @var string
     */
    protected $key = 'traffic-commissioner-publication';

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
                new Filter\LicenceType(),
                new Filter\TrafficArea(),
                new Filter\GoodsOrPsv(),
                new Filter\PublicationType(),
                new Filter\PublicationSection(),
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
            ['title' => 'Forename', 'name' => 'pubNo'],
            ['title' => 'Family name', 'name' => 'pubSecDesc'],
        ];
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
                    'options' => [10, 25, 50]
                ]
            ],
            'layout' => 'traffic-commissioner-publication-selfserve',
            'show-status' => false
        ];
    }
}
