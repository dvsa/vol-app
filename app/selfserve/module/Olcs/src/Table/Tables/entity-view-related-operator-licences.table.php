<?php

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
                    ) . '">' . $data['licNo'] . '</a>';
                }
                return '';
            }
        ),
        array(
            'title' => 'search-result-label-licence-status',
            'formatter' => 'RefData',
            'name' => 'status'
        ),
        array(
            'title' => 'search-result-label-continuation-date',
            'formatter' => 'Date',
            'name' => 'expiryDate'
        )
    )
);
