<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\Translate;
use Common\Util\Escape;

$variationNo = 1;
return [
    'variables' => [
        'title' => 'Registration history'
    ],
    'settings' => [],
    'columns' => [
        [
            'title' => 'Reg No.',
            'formatter' => function ($data) {
                if (isset($data['id'])) {
                    if ((bool)$this->getVariable('isSearchPage') === true) {
                        return sprintf(
                            '<a href="%s" class="govuk-link">%s</a>',
                            $this->generateUrl(['busRegId' => $data['id']], 'search-bus/details', false),
                            $data['regNo']
                        );
                    } else {
                        return '<a href="' . $this->generateUrl(
                            ['action' => 'details', 'busRegId' => $data['id']],
                            'bus-registration/details',
                            false
                        ) . '" class="govuk-link">' . $data['regNo'] . '</a>';
                    }
                }
                return '';
            }
        ],
        [
            'title' => 'Var No.',
            'isNumeric' => true,
            'name' => 'variationNo'
        ],
        [
            'title' => 'Status',
            'formatter' => Translate::class,
            'name' => 'status->description',
        ],
        [
            'title' => 'Application type',
            'formatter' => function ($data, $column) {
                if ($data['isTxcApp'] == 'Y') {
                    if ($data['ebsrRefresh'] == 'Y') {
                        return $this->translator->translate('EBSR Data Refresh');
                    } else {
                        return $this->translator->translate('EBSR');
                    }
                } else {
                    return $this->translator->translate('Manual');
                }
            }
        ],
        [
            'title' => 'Date received',
            'formatter' => Date::class,
            'name' => 'receivedDate'
        ],
        [
            'title' => 'Date effective',
            'formatter' => Date::class,
            'name' => 'effectiveDate'
        ],
        [
            'title' => 'End date',
            'formatter' => Date::class,
            'name' => 'endDate'
        ],
        [
            'title' => 'Action',
            'width' => 'checkbox',
            'formatter' => function ($data) {
                if (isset($data['canDelete'])) {
                    return '<input type="radio" aria-label="Delete ' . Escape::htmlAttr($data['regNo']) . '" name="id" value="' . Escape::htmlAttr($data['id']) . '">';
                }
            },
        ]
    ]
];
