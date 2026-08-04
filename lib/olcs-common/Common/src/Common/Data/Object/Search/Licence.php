<?php

namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms as Filter;
use Common\Service\Table\Formatter\SearchAddressOperatorName;
use Common\Service\Table\Formatter\SearchLicenceCaseCount;

/**
 * Class Licence
 * @package Common\Data\Object\Search
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
    #[\Override]
    public function getFilters()
    {
        if (empty($this->filters)) {
            $this->filters = [
                new Filter\LicenceType(),
                new Filter\LicenceStatus(),
                new Filter\LicenceTrafficArea(),
                new Filter\EntityType(),
                new Filter\GoodsOrPsv(),
            ];
        }

        return $this->filters;
    }

    /**
     * get settings
     *
     * @return array
     */
    #[\Override]
    public function getSettings()
    {
        return [
            'crud' => [
                'links' => [
                    'create-operator' => [
                        'label' => 'Create operator',
                        'class' => 'govuk-button js-modal-ajax',
                        'route' => [
                            'route' => 'create_operator'
                        ]
                    ],
                    'create-unlicensed-operator' => [
                        'label' => 'Create unlicensed operator',
                        'class' => 'govuk-button js-modal-ajax',
                        'route' => [
                            'route' => 'create_unlicensed_operator'
                        ]
                    ]
                ]
            ],
            'paginate' => [
                'limit' => [
                    'options' => [10, 25, 50]
                ]
            ]
        ];
    }

    /**
     * get columns
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
                'formatter' => static fn($data) => '<a class="govuk-link" href="/licence/' . $data['licId'] . '">' . $data['licNo'] . '</a>'
            ],
            ['title' => 'Licence status', 'name' => 'licStatusDesc'],
            [
                'title' => 'Operator name',
                'name' => 'orgName',
                'formatter' => SearchAddressOperatorName::class
            ],
            [
                'title' => 'Trading name',
                'name' => 'licenceTradingNames',
                'formatter' => static fn($data) => str_replace('|', ', <br />', $data['licenceTradingNames'])
            ],
            ['title' => 'Entity type', 'name' => 'orgTypeDesc'],
            ['title' => 'Licence type', 'name' => 'licTypeDesc'],
            ['title' => 'FABS Reference', 'name' => 'fabsReference'],
            [
                'title' => 'Cases',
                'name' => 'caseCount',
                'formatter' => SearchLicenceCaseCount::class
            ]
        ];
    }
}
