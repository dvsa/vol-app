<?php

use Common\Service\Table\Formatter\ConvictionDescription;
use Common\Service\Table\Formatter\Date;

return array(
    'variables' => array(
        'title' => 'Convictions',
        'titleSingular' => 'Conviction',
        'empty_message' => 'There are no convictions'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'conviction',
            'actions' => array(
                'add' => array('class' => 'govuk-button', 'label' => 'Add conviction'),
                'edit' => array('requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'),
                'delete' => array('requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one')
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        ),
        'useQuery' => true
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Date of conviction',
            'formatter' => function ($data, $column) {

                $url = $this->generateUrl(['action' => 'edit', 'conviction' => $data['id']], 'conviction', true);
                $class = 'govuk-link js-modal-ajax';
                if ($data['convictionDate'] == null) {
                    return '<a href="' . $url . '" class="' . $class . '">N/A</a>';
                }

                $column['formatter'] = Date::class;
                return '<a href="' . $url . '" class="' . $class . '">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'convictionDate'
        ),
        array(
            'title' => 'Date of offence',
            'formatter' => Date::class,
            'name' => 'offenceDate'
        ),
        array(
            'title' => 'Name / defendant type',
            'formatter' => function ($data, $column) {

                                $person = $data['personFirstname'] . ' ' . $data['personLastname'];
                $organisationName = $data['operatorName'];
                $name = ($organisationName == '' ? $person : $organisationName) . ' <br /> '
                      . $this->translator->translate($data['defendantType']['description']);

                return $name;
            }
        ),
        array(
            'title' => 'Description',
            'formatter' => ConvictionDescription::class,
        ),
        array(
            'title' => 'Court/FPN',
            'name' => 'court'
        ),
        array(
            'title' => 'Penalty',
            'name' => 'penalty'
        ),
        array(
            'title' => 'SI',
            'name' => 'msi'
        ),
        array(
            'title' => 'Declared',
            'name' => 'isDeclared'
        ),
        array(
            'title' => 'Dealt with',
            'name' => 'isDealtWith'
        )
    )
);
