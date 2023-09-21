<?php

use Common\Service\Table\Formatter\Date;

return array(
    'variables' => array(
        'titleSingular' => 'GV Permit',
        'title' => 'GV Permits'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'govuk-button'),
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
            'title' => 'Permit Id',
            'isNumeric' => true,
            'formatter' => function ($data) {
                return '<a href="' . $this->generateUrl(
                    array('action' => 'details', 'id' => $data['id']),
                    'operator/irfo/gv-permits',
                    true
                ) . '" class="govuk-link js-modal-ajax">' . $data['id'] . '</a>';
            }
        ),
        array(
            'title' => 'In force date',
            'formatter' => Date::class,
            'name' => 'inForceDate'
        ),
        array(
            'title' => 'Type',
            'formatter' => function ($data, $column) {
                return $data['irfoGvPermitType']['description'];
            }
        ),
        array(
            'title' => 'Status',
            'formatter' => function ($data, $column) {
                return $data['irfoPermitStatus']['description'];
            }
        )
    )
);
