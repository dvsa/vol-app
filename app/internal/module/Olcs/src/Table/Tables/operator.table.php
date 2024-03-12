<?php

use Common\Service\Table\Formatter\Date;

return [
    'variables' => [
        'title' => 'Operators',
        'titleSingular' => 'Operator',
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ],
        'crud' => [
            'actions' => [
                'createOperator' => ['class' => 'govuk-button', 'value' => 'Create operator'],
                'createTransportManager' => ['class' => 'govuk-button govuk-button--secondary', 'value' => 'Create transport manager']
            ]
        ],
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Lic no/status',
            'sort' => 'licNo',
            'formatter' => fn($row) => '<a class="govuk-link" href="' . $this->generateUrl(
                ['licence' => $row['licenceId']],
                'licence'
            ) . '">' . $row['licNo'] . '</a><br/>' . $row['status'],
        ],
        [
            'title' => 'App ID/status',
            'isNumeric' => true,
            'format' => '{{appNumber}}<br/>{{appStatus}}',
            'sort' => 'appId'
        ],
        [
            'title' => 'Op/trading name',
            'formatter' => fn($data) => '<a class="govuk-link" href="' . $this->generateUrl(
                ['operator' => $data['organisation_id']],
                'operator/business-details'
            ) . '">' . $data['name'] . '</a><br/>' . $data['status'],
            'sort' => 'operatorName'
        ],
        [
            'title' => 'Entity / Lic Type',
            'format' => '{{organisation_type}} / {{licence_type}}',
        ],
        [
            'title' => 'Last act CN/Date',
            'name' => 'last_updated_on',
            'formatter' => Date::class,
            'sort' => 'lastActionDate'
        ],
        [
            'title' => 'Correspondence address',
            'formatter' => function ($data) {
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
                    return '<a class="govuk-link" href="' . $this->generateUrl(
                        ['licence' => $data['licenceId']],
                        'licence/cases',
                        false
                    ) . '">' . $data['caseCount'] . '</a>';
                } else {
                    return '<a class="govuk-link" href="' . $this->generateUrl(
                        ['licence' => $data['licenceId'], 'action' => 'add'],
                        'case'
                    ) . '">[Add Case]</a>';
                }
            }
        ],
        [
            'title' => 'MLH',
            'format' => '[MLH]'
        ],
        [
            'title' => 'Info',
            'formatter' => function ($data, $column) {
                $string = '<span class="tooltip">';
                $string .= !empty($data['startDate']) ?
                        ucfirst($this->translator->translate('start')) . ': ' .
                        $data['startDate'] . '<br />' : '';
                $string .= !empty($data['reviewDate']) ?
                        ucfirst($this->translator->translate('review')) . ': ' .
                        $data['reviewDate'] . '<br />' : '';
                $string .= !empty($data['endDate']) ?
                        ucfirst($this->translator->translate('end')) . ': ' .
                        $data['endDate'] . '<br />' : '';
                $string .= !empty($data['fabsReference']) ?
                        ucfirst($this->translator->translate('fabs-reference')) . ': ' .
                        $data['fabsReference'] . '<br />' : '';
                $string .= '</span>';

                if ($string == '<span class="tooltip"></span>') {
                    $string = '';
                }
                return $string;
            }
        ]
    ]
];
