<?php

use Common\Service\Table\Formatter\Date;
use Olcs\Module;

return array(
    'variables' => array(
        'title' => 'Not Pi',
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'NonPi',
            'actions' => array(
                'add' => array('class' => 'govuk-button'),
                'edit' => array('requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'),
                'delete' => array('requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one')
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Meeting date',
            'formatter' => function ($data, $column) {
                $url = $this->generateUrl(
                    ['action' => 'edit', 'id' => $data['id']],
                    'case_non_pi', true
                );
                $column['formatter'] = Date::class;
                return '<a class="govuk-link" href="' . $url . '">' . date(Module::$dateTimeSecFormat, strtotime($data['hearingDate'])) . '</a>';
            },
            'name' => 'id'
        ),
        array(
            'title' => 'Meeting venue',
            'formatter' => function ($data) {
                return (isset($data['venue']['name']) ? $data['venue']['name'] : $data['venueOther']);
            }
        ),
        array(
            'title' => 'Witness Count',
            'isNumeric' => true,
            'name' => 'witnessCount'
        ),
        array(
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        )
    )
);
