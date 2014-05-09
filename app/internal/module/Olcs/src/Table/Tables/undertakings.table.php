<?php

return array(
    'variables' => array(
        'title' => 'Undertakings'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'No.',
            'name' => 'id'
        ),
        array(
            'title' => 'Added via',
            'formatter' => function ($data, $column) {
                return 'Case ' . $data['caseId'];
            },
            'name' => 'caseId'
        ),
        array(
            'title' => 'Fulfilled',
            'formatter' => function ($data, $column) {
                return $data['isFulfilled'] ? 'Yes' : 'No';
            },
        ),
        array(
            'title' => 'Status',
            'formatter' => function ($data, $column) {
                return $data['isDraft'] ? 'Draft' : 'Approved';
            },
        ),
        array(
            'title' => 'Attached to',
            'name' => 'attachedTo'
        ),
        array(
            'title' => 'S4',
            'formatter' => function ($data, $column) {
                return 'ToDo'; // todo S4 clarification
            }
        ),
        array(
            'title' => 'OC Address',
            'formatter' => function ($data, $column) {
                // TODO remove this once address entity has been finalised
                if (isset($data['operatingCentre']['address'])) {
                    $data['operatingCentre']['address']['paon_desc'] =
                            isset($data['operatingCentre']['address']['paon_desc']) ?
                            $data['operatingCentre']['address']['paon_desc'] :
                            $data['operatingCentre']['address']['addressLine2'];
                    $data['operatingCentre']['address']['street'] =
                            isset($data['operatingCentre']['address']['street']) ?
                            $data['operatingCentre']['address']['street'] :
                            $data['operatingCentre']['address']['addressLine3'];
                    $data['operatingCentre']['address']['locality'] =
                            isset($data['operatingCentre']['address']['locality']) ?
                            $data['operatingCentre']['address']['locality'] :
                            $data['operatingCentre']['address']['addressLine4'];
                    $data['operatingCentre']['address']['postcode'] =
                            isset($data['operatingCentre']['address']['postcode']) ?
                            $data['operatingCentre']['address']['postcode'] :
                            'postcode';
                    $data['operatingCentre']['address']['country'] =
                            isset($data['operatingCentre']['address']['country']) ?
                            $data['operatingCentre']['address']['country'] :
                            'country';

                    return $data['operatingCentre']['address']['paon_desc'] . ', ' .
                            $data['operatingCentre']['address']['street'] . ', ' .
                            $data['operatingCentre']['address']['locality'] . ', ' .
                            $data['operatingCentre']['address']['postcode'] . ', ' .
                            $data['operatingCentre']['address']['country'];
                } else {
                    return '';
                }
            }
        ),
    )
);
