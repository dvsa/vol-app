<?php

use Common\Service\Table\Formatter\Date;

return array(
    'variables' => array(
        'title' => ' open cases associated with this licence'
    ),
    'attributes' => array(
        'name' => 'openCases'
    ),
    'settings' =>[
        'showTotal'=>true
    ],
    'columns' => array(
        array(
            'title' => 'Case No.',
            'isNumeric' => true,
            'formatter' => function ($row) {
                return '<a class="govuk-link" href="' . $this->generateUrl(
                    array('case' => $row['id'], 'action' => 'details'),
                    'case',
                    true
                ) . '">' . $row['id'] . '</a>';
            },
            'sort' => 'id'
        ),
        array(
            'title' => 'Case type',
            'formatter' => function ($row, $column) {
                if (isset($row['caseType']['description'])) {
                    return $this->translator->translate($row['caseType']['description']);
                } else {
                    return 'Not set';
                }
            },
            'sort' => 'caseType'
        ),
        array(
            'title' => 'Created',
            'formatter' => Date::class,
            'name' => 'createdOn',
            'sort' => 'createdOn'
        ),

        array(
            'title' => 'Description',
            'formatter' => \Common\Service\Table\Formatter\Comment::class,
            'maxlength' => 250,
            'append' => '...',
            'name' => 'description'
        ),
    )
);
