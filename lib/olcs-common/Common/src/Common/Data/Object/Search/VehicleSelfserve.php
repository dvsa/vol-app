<?php

namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms as Filter;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\Translate;

/**
 * Class Vehicle
 * @package Common\Data\Object\Search
 */
class VehicleSelfserve extends InternalSearchAbstract
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
                new Filter\LicenceType(),
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
                'formatter' => static fn($data) => '<a class="govuk-link" href="/view-details/licence/' . $data['licId'] . '">' . $data['licNo'] . '</a>'
            ],
            [
                'title' => 'Licence status',
                'name' => 'licStatusDesc',
                'formatter' => Translate::class,
            ],
            [
                'title' => 'Operator name',
                'name' => 'orgName'
            ],
            ['title' => 'VRM', 'name' => 'vrm'],
            ['title' => 'Disc Number', 'name' => 'discNo'],
            [
                'title' => 'Specified date',
                'formatter' => Date::class,
                'name' => 'specifiedDate'
            ],
            [
                'title' => 'Removed date',
                'formatter' => Date::class,
                'name' => 'removalDate'
            ],
        ];
    }
}
