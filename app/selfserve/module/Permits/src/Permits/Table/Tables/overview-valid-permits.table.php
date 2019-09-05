<?php

use Common\Util\Escape;
use Common\RefData;

return array(
    'variables' => array(),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            ),
        ),
    ),
    'attributes' => array(),
    'columns' => array(
        array(
                'title' => 'permits.ecmt.page.valid.tableheader.permit-no',
                'name' => 'permitNumber',
                'formatter' => function ($row) {
                    return '<b>' . Escape::html($row['permitNumber']) . '</b>';
                },
            ),
        array(
            'title' => 'permits.ecmt.page.valid.tableheader.application-no',
            'name' => 'irhpPermitApplication',
            'stack' => 'irhpPermitApplication->relatedApplication->id',
            'formatter' => 'StackValue',
        ),
        array(
            'title' => 'permits.ecmt.page.valid.tableheader.min-emission',
            'name' => 'emissionsCategory',
            'formatter' => 'RefData',
        ),
        array(
            'title' => 'permits.ecmt.page.valid.tableheader.countries',
            'name' => 'countries',
            'formatter' => function ($row, $column, $sm) {
                $translator = $sm->get('translator');
                if (count($row['countries']) === 0) {
                    return $translator->translate('permits.ecmt.page.valid.no.countries');
                }
                $rc = [];
                foreach ($row['countries'] as $country) {
                    $rc[] = $translator->translate($country['countryDesc']);
                }
                return Escape::html(implode(', ', $rc));
            }
        ),
        array(
            'title' => 'permits.ecmt.page.valid.tableheader.start-date',
            'name' => 'startDate',
            'formatter' => 'Date',
        ),
        array(
            'title' => 'permits.ecmt.page.valid.tableheader.expiry-date',
            'name' => 'expiryDate',
            'formatter' => 'Date',
        ),
        array(
            'title' => 'permits.ecmt.page.valid.tableheader.status',
            'name' => 'status',
            'formatter' => function ($row) {
                return $this->callFormatter(
                    [
                        'name' => 'status',
                        'formatter' => 'RefDataStatus',
                    ],
                    [
                        'status' => [
                            'id' => RefData::PERMIT_VALID,
                            'description' => RefData::PERMIT_VALID
                        ],
                    ]
                );
            }
        ),
    )
);
