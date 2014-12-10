<?php

return array(
    'columns' => array(
        array(
            'title' => 'No.',
            'name' => 'id'
        ),
        array(
            'title' => 'Added via',
            'formatter' => function ($data, $column, $sl) {
                return $sl->get('translator')->translate($data['addedVia']['description']);
            },
        ),
        array(
            'title' => 'Fulfilled',
            'formatter' => function ($data, $column) {
                return $data['isFulfilled'] == 'Y' ? 'Yes' : 'No';
            },
        ),
        array(
            'title' => 'Status',
            'formatter' => function ($data, $column) {
                return $data['isDraft'] == 'Y' ? 'Draft' : 'Approved';
            },
        ),
        array(
            'title' => 'Attached to',
            'formatter' => function ($data, $column, $sm) {
                return $sm->get('translator')->translate($data['attachedTo']['description']);
            },
        ),
        array(
            'title' => 'S4',
            'formatter' => function ($data, $column) {
                return 'ToDo';
            }
        ),
        array(
            'title' => 'OC address',
            'width' => '300px',
            'formatter' => function ($data, $column, $sm) {

                if (isset($data['operatingCentre']['address'])) {

                    $column['formatter'] = 'Address';

                    return $this->callFormatter($column, $data['operatingCentre']['address']);
                }

                return 'N/a';
            }
        ),
    )
);
