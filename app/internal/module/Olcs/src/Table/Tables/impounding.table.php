<?php

return array(
    'variables' => array(
        'title' => 'Impounding'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'action--primary'),
                'edit' => array('requireRows' => true, 'class' => 'action--secondary js-require--one'),
                'generate' => array(
                    'requireRows' => true,
                    'class' => 'action--secondary js-require--one',
                    'label' => 'Generate Letter'
                ),
                'delete' => array('requireRows' => true, 'class' => 'action--secondary js-require--one')
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
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
            'title' => 'Application received',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'Date';
                return '<a href="' . $this->generateUrl(
                    array('action' => 'edit', 'impounding' => $data['id']),
                    'case_details_impounding',
                    true
                ) . '" class="js-modal-ajax">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'applicationReceiptDate'
        ),
        array(
            'title' => 'Type',
            'formatter' => function ($data, $column, $sm) {
                return $sm->get('translator')->translate($data['impoundingType']['id']);
            }
        ),
        array(
            'title' => 'Presiding TC/DTC/HTRU/DHTRU',
            'formatter' => function ($data) {
                return (isset($data['presidingTc']['name']) ? $data['presidingTc']['name'] : '');
            }
        ),
        array(
            'title' => 'Outcome',
            'formatter' => function ($data, $column, $sm) {
                return (isset($data['outcome']['id']) ? $sm->get('translator')->translate($data['outcome']['id']) : '');
            }
        ),
        array(
            'title' => 'Outcome sent',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'Date';
                return $this->callFormatter($column, $data);
            },
            'name' => 'outcomeSentDate'

        ),
    )
);
