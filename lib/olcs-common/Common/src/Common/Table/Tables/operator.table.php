<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'title' => 'Result list'
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'options' => [10, 25, 50]
            ]
        ]
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Lic no/status',
            'format' => '<a class="govuk-link" href="#">{{licNo}}</a><br/>{{status}}',
            'sort' => 'licNo'
        ],
        [
            'title' => 'App ID/status',
            'format' => '{{appNumber}}<br/>{{appStatus}}',
            'sort' => 'appId'
        ],
        [
            'title' => 'Op/trading name',
            'formatter' => static fn($data) => $data['trading_as'] ? : $data['name'],
            'sort' => 'operatorName'
        ],
        [
            'title' => 'Company/Lic type',
            'name' => 'licenceType'
        ],
        [
            'title' => 'Last act CN/Date',
            'name' => 'last_updated_on',
            'formatter' => Date::class,
            'sort' => 'lastActionDate'
        ],
        [
            'title' => 'Correspondence address',
            'formatter' => static function ($data) {
                $parts = [];
                foreach (['address_line1', 'address_line2', 'address_line3', 'postcode'] as $item) {
                    if (!empty($data[$item])) {
                        $parts[] = $data[$item];
                    }
                }
                return implode(', ', $parts);
            },
            'sort' => 'correspondenceAddress'
        ],
        [
            'title' => 'Cases',
            'formatter' => function ($data) {
                if (isset($data['caseCount']) && (int) $data['caseCount'] > 0) {
                    /**
                     * @var TableBuilder $this
                     * @psalm-scope-this TableBuilder
                     */
                    return '<a class="govuk-link" href="' . $this->generateUrl(
                        ['licence' => $data['licenceId']],
                        'licence_case_list/pagination',
                        false
                    ) . '">' . $data['caseCount'] . '</a>';
                }
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                return '<a class="govuk-link" href="' . $this->generateUrl(
                    ['licence' => $data['licenceId'], 'action' => 'add'],
                    'licence_case_action'
                ) . '">[Add Case]</a>';
            }
        ],
        [
            'title' => 'MLH',
            'format' => '[MLH]'
        ],
        [
            'title' => 'Info',
            'format' => '[Info]'
        ]
    ]
];
