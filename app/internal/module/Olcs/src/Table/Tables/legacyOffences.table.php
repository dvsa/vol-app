<?php

use Common\Service\Table\Formatter\Date;

return array(
    'variables' => array(
        'title' => 'Legacy offences',
        'titleSingular' => 'Legacy offence',
        'empty_message' => 'There are no legacy offences'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'offence',
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
            'title' => 'Offence date from',
            'formatter' => function ($data, $column) {
                $url = $this->generateUrl(['action' => 'details', 'id' => $data['id']], 'offence', true);
                $class = 'govuk-link js-modal-ajax';

                if ($data['offenceDate'] == null) {
                    return '<a href="' . $url . '" class="' . $class . '">N/A</a>';
                }

                $column['formatter'] = Date::class;
                return '<a href="' . $url . '" class="' . $class . '">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'offenceDate'
        ),
        array(
            'title' => 'Originating authority',
            'name' => 'offenceAuthority'
        ),
        array(
            'title' => 'Vehicle',
            'name' => 'vrm'
        ),
        array(
            'title' => 'Trailer',
            'name' => 'isTrailer'
        ),
        array(
            'title' => 'Offence detail',
            'name' => 'notes',
            'formatter' => \Common\Service\Table\Formatter\Comment::class,
            'maxlength' => 150,
            'append' => '...'
        )
    )
);
