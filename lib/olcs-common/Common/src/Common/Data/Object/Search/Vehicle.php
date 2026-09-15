<?php

namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms as Filter;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\SearchAddressOperatorName;

/**
 * Class Vehicle
 * @package Common\Data\Object\Search
 */
class Vehicle extends InternalSearchAbstract
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
     * Returns an array of filters for this index
     *
     * @return array
     */
    #[\Override]
    public function getFilters()
    {
        if ($this->filters === []) {
            $this->filters = [
                new Filter\LicenceStatus(),
            ];
        }

        return $this->filters;
    }

    #[\Override]
    public function getSettings()
    {
        $settings = parent::getSettings();

        return array_merge(
            $settings,
            [
                'crud' => [
                    'actions' => [
                        'vehicleSet26' => [
                            'class' => 'govuk-button govuk-button--secondary js-require--multiple',
                            'requireRows' => true,
                            'label' => 'Set Sec26',
                        ],
                        'vehicleRemove26' => [
                            'class' => 'govuk-button govuk-button--secondary js-require--multiple',
                            'requireRows' => true,
                            'label' => 'Remove section 26'
                        ],
                    ]
                ],
            ]
        );
    }

    /**
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
                'title' => 'VRM',
                'formatter' => static function ($data) {
                    $section26 = (isset($data['section_26']) && $data['section_26']) ? ' (sec26)' : '';
                    return $data['vrm'] . $section26;
                }
            ],
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
            [
                'title' => '',
                'width' => 'checkbox',
                'type' => 'Checkbox',
                'idIndex' => 'vehId',
            ],
        ];
    }
}
