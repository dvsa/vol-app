<?php

namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms as Filter;
use Common\Service\Table\Formatter\Translate;

/**
 * Class Licence
 * @package Common\Data\Object\Search
 */
class LicenceSelfserve extends InternalSearchAbstract
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
                new Filter\EntityType(),
                new Filter\LicenceType(),
                new Filter\LicenceStatus(),
                new Filter\LicenceTrafficArea(),
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
                'name' => 'orgName',
                'formatter' => static fn($data) => $data['orgName'] . ($data['noOfLicencesHeld'] > 1 ? ' (MLH)' : ''),
            ],
            [
                'title' => 'Trading name',
                'name' => 'licenceTradingNames',
                'formatter' => static fn($data) => str_replace('|', ', <br />', $data['licenceTradingNames'])
            ]
        ];
    }
}
