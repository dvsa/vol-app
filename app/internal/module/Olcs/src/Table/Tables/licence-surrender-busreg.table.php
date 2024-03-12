<?php

use Common\Service\Table\Formatter\Date;

return [
    'variables' => [
        'title' => ' active bus registrations associated with this licence.'
    ],
    'attributes' => [
        'name'=>'busRegistrations'
    ],
    'settings' =>[
        'showTotal'=>true
    ],
    'columns' => [
        [
            'title' => 'Reg No.',
            'formatter' => fn($data) => '<a class="govuk-link" href="' . $this->generateUrl(
                ['action' => 'index', 'busRegId' => $data['id']],
                'licence/bus-details/service',
                true
            ) . '">' . $data['regNo'] . '</a>',
        ],
        [
            'title' => 'Var No.',
            'isNumeric' => true,
            'name' => 'variationNo',
        ],
        [
            'title' => 'Service No.',
            'isNumeric' => true, //mostly numeric so using the style
            'name' => 'serviceNo',
        ],
        [
            'title' => '1st registered / cancelled',
            'formatter' => Date::class,
            'name' => 'date1stReg'
        ],
        [
            'title' => 'Starting point',
            'name' => 'startPoint',
        ],
        [
            'title' => 'Finishing point',
            'name' => 'finishPoint',
        ],
        [
            'title' => 'Status',
            'name' => 'busRegStatusDesc'
        ],
    ]
];
