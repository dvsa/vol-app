<?php

return array(
    'variables' => array(
        'title' => 'IRFO PSV Authorisations'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'renew' => array(
                    'label' => 'Set to renew', 
                    'class' => 'action--primary js-require--multiple', 
                    'requireRows' => true
                ),
                'print' => array(
                    'label' => 'Print checklist', 
                    'class' => 'action--secondary js-require--multiple', 
                    'requireRows' => true
                )
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 25,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Auth Id',
            'formatter' => function ($data) {
                return sprintf(
                    '<a href="%s" class="js-modal-ajax">%s</a>',
                    $this->generateUrl(
                        array('action' => 'edit', 'id' => $data['id'], 'organisation' => $data['organisation']['id']),
                        'operator/irfo/psv-authorisations',
                        false
                    ),
                    $data['id']
                );
            }
        ),
        array(
            'title' => 'Operator',
            'formatter' => function ($data) {
                return sprintf(
                    '<a href="%s" class="js-modal-ajax">%s</a>',
                    $this->generateUrl(
                        array('action' => 'edit', 'organisation' => $data['organisation']['id']),
                        'operator/irfo/details',
                        false
                    ),
                    $data['organisation']['name']
                );
            }
        ),
        array(
            'title' => 'In force date',
            'name' => 'inForceDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => 'Expiry date',
            'name' => 'expiryDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => 'Status',
            'formatter' => function ($data) {
                return $data['status']['description'];
            }
        ),
        array(
            'title' => 'Type',
            'formatter' => function ($data) {
                return $data['irfoPsvAuthType']['description'];
            }
        ),
        array(
            'type' => 'Checkbox',
            'width' => 'checkbox',
        ),
    )
);
