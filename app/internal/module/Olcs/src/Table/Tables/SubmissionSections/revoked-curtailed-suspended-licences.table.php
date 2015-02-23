<?php
return array(
    'variables' => array(
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'tm-previous-history', 'subSection' => 'revoked-curtailed-suspended-licences']
        ],
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'revoked-curtailed-suspended-licences',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'secondary', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display',
    ),
    'attributes' => array(
        'name' => 'revoked-curtailed-suspended-licences'
    ),
    'columns' => array(
        array(
            'title' => 'Licence No.',
            'name' => 'licNo',
        ),
        array(
            'title' => 'Licence holder name',
            'name' => 'holderName'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ),
    )
);
