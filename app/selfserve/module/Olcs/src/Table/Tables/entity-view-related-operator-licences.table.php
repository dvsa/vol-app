<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\RefData;

return array(
    'variables' => array(
        'empty_message' => 'entity-view-table-related-operator-licences.table.empty',
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'search-result-label-lic-no',
            'formatter' => function ($data) {
                if (isset($data['id'])) {
                    return '<a href="' . $this->generateUrl(
                        array('entity' => 'licence', 'entityId' => $data['id']),
                        'entity-view',
                        false
                    ) . '" class="govuk-link">' . $data['licNo'] . '</a>';
                }
                return '';
            }
        ),
        array(
            'title' => 'search-result-label-licence-status',
            'formatter' => RefData::class,
            'name' => 'status'
        ),
        array(
            'title' => 'search-result-label-continuation-date',
            'formatter' => Date::class,
            'name' => 'expiryDate'
        )
    )
);
