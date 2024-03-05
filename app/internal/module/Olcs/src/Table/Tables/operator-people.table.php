<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\DisqualifyUrl;
use Common\Service\Table\Formatter\Name;

return array(
    'variables' => array(
        'titleSingular' => 'Person',
        'title' => 'People',
        'empty_message' => 'selfserve-app-subSection-your-business-people-other.table.empty-message',
        'required_label' => 'person',
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'govuk-button'),
                'edit' => array('requireRows' => true),
                'delete' => array(
                    'label' => 'people_table_action.delete.label',
                    'class' => 'govuk-button govuk-button--warning',
                    'requireRows' => true,
                ),
            )
        )
    ),
    'columns' => array(
        array(
            'title' => 'selfserve-app-subSection-your-business-people-columnName',
            'type' => 'Action',
            'action' => 'edit',
            'formatter' => Name::class
        ),
        array(
            'title' => 'selfserve-app-subSection-your-business-people-columnHasOtherNames',
            'name' => 'otherName',
            'formatter' => function ($row) {
                return ($row['otherName'] ? 'Yes' : 'No');
            }
        ),
        array(
            'title' => 'selfserve-app-subSection-your-business-people-columnDate',
            'name' => 'birthDate',
            'formatter' => Date::class,
        ),
        array(
            'title' => 'selfserve-app-subSection-your-business-people-columnPosition',
            'name' => 'position',
        ),
        array(
            'title' => 'Disqual',
            'formatter' => DisqualifyUrl::class,
        ),
        array(
            'name' => 'select',
            'width' => 'checkbox',
            'type' => 'Checkbox'
        )
    )
);
