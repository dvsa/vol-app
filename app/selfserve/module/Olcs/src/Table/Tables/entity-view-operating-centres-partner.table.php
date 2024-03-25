<?php

use Common\Service\Table\Formatter\Address;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\YesNo;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [],
    'settings' => [],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'entity-view-label-operating-centre',
            'addressFields' => 'FULL',
            'formatter' => Address::class,
            'name' => 'operatingCentre->address'
        ],
        [
            'title' => 'entity-view-table-header-interim',
            'formatter' => YesNo::class,
            'name' => 'isInterim'
        ],
        [
            'title' => 'entity-view-table-header-vehicles-authorised',
            'isNumeric' => true,
            'formatter' => fn($data) => !empty($data['noOfVehiclesRequired']) ?
                $data['noOfVehiclesRequired'] : '0',
            'name' => 'noOfVehiclesRequired'
        ],
        [
            'title' => 'entity-view-table-header-trailers-authorised',
            'isNumeric' => true,
            'formatter' => fn($data) => !empty($data['noOfTrailersRequired']) ?
                $data['noOfTrailersRequired'] : '0'
        ],
        [
            'title' => 'entity-view-table-header-date-added',
            'name' => 'createdOn',
            'formatter' => function ($row, $column) {
                $column['formatter'] = Date::class;
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                return $this->callFormatter($column, $row['operatingCentre']);
            }
        ],
        [
            'title' => 'entity-view-table-header-date-removed',
            'name' => 'deletedDate',
            'formatter' => function ($row, $column) {
                $column['formatter'] = Date::class;
                if (empty($row['deletedDate'])) {
                    return 'NA';
                }
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                return $this->callFormatter($column, $row['deletedDate']);
            }
        ]
    ]
];
