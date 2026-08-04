<?php

namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms as Filter;
use Common\Service\Table\Formatter\SearchOperatingCentreSelfserveLicNo;

/**
 * Class Address
 * @package Common\Data\Object\Search
 */
class OperatingCentreSelfserve extends InternalSearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Operating centres';

    /**
     * @var string
     */
    protected $key = 'address';

    /**
     * @var string
     */
    protected $searchIndices = 'address';

    /**
     * Contains an array of the instantiated filters classes.
     *
     * @var array
     */
    protected $filters = [];

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
     * Returns an array of filters for this index
     *
     * @return array
     */
    #[\Override]
    public function getFilters()
    {
        if (empty($this->filters)) {
            $this->filters = [
                new Filter\LicenceStatus(),
                new Filter\GoodsOrPsv(),
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
                'title' => 'Licence number',
                'name' => 'licNo',
                'formatter' => SearchOperatingCentreSelfserveLicNo::class
            ],
            [
                'title' => 'Operator name',
                'name' => 'orgName'
            ],
            [
                'title' => 'Address',
                'formatter' => \Common\Service\Table\Formatter\Address::class,
                'addressFields' => [
                    'street', 'locality', 'town', 'postcode'
                ]
            ],
        ];
    }
}
