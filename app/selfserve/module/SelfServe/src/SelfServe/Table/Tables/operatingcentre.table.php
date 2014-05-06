<?php

return array(
    'variables' => array(
        'title' => 'Operating centres'
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
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Operating centre address',
            'formatter' => function ($data, $column) {

                $sl = $this->getServiceLocator();
                $applicationId = $sl->get('Application')->getMvcEvent()->getRouteMatch()->getParam('applicationId');

                $column['formatter'] = 'Address';

                return "<a href='" . $this->generateUrl(
                    array(
                        'action' => 'edit',
                        'id' => $data['id'],
                        'applicationId' => $applicationId
                    ),
                    'selfserve/finance/operating_centre',
                    false
                ) . "'>" . $this->callFormatter($column, $data) . "</a>";
            },
            'name' => 'address'
        ),
        array(
            'title' => 'Vehicles',
            'name' => 'numberOfVehicles'
        ),
        'trailersCol' => array(
            'title' => 'Trailers',
            'name' => 'numberOfTrailers'
        ),
        array(
            'title' => 'Permission',
            'name' => 'permission',
            'formatter' => 'YesNo'
        ),
        array(
            'title' => 'Advertised',
            'name' => 'adPlaced',
            'formatter' => 'YesNo'
        )
    ),
    // Footer configuration
    'footer' => array(
        array(
            'type' => 'th',
            'format' => 'Total vehicles and trailers', // i.e. 'Title: {{title}}'
            'colspan' => 2
        ),
        array(
            'formatter' => 'Sum',
            'name' => 'numberOfVehicles'
        ),
        array(
            'formatter' => 'Sum',
            'name' => 'numberOfTrailers'
        ),
        array(
            'colspan' => 2
        )
    )
);
