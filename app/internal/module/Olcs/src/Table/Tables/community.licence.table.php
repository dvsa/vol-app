<?php

return array(
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 50,
                'options' => array(50)
            )
        ),
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'action--secondary', 'value' => 'Add'),
                'office-licence-add' => array('class' => 'action--secondary', 'value' => 'Add office licence')
            )
        ),
        'row-disabled-callback' => function ($row) {
            return ($row['status']['id'] !== 'cl_sts_active');
        },
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Prefix',
            'sort' => 'prefix',
            'name' => 'serialNoPrefix',
        ),
        array(
            'title' => 'Date Issued',
            'formatter' => 'Date',
            'sort' => 'specifiedDate',
            'name' => 'specifiedDate',
        ),
        array(
            'title' => 'Issue number',
            'sort' => 'issueNo',
            'name' => 'issueNo',
            'formatter' => 'CommunityLicenceIssueNo',
        ),
        array(
            'title' => 'Status',
            'name' => 'status',
            'formatter' => 'CommunityLicenceStatus'
        ),
        array(
            'type' => 'Checkbox',
            'title' => '',
            'width' => 'checkbox',
            'disableIfRowIsDisabled' => true
        ),
    )
);
