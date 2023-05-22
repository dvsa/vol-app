<?php

return array(
    'variables' => array(
        'titleSingular' => 'Community Licence',
        'title' => 'Community Licences',
        'within_form' => true,
    ),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 50,
                'options' => array(50)
            )
        ),
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'govuk-button govuk-button--secondary', 'value' => 'Add'),
                'office-licence-add' => array('class' => 'govuk-button govuk-button--secondary', 'value' => 'Add office licence'),
                'restore' => array('class' => 'govuk-button govuk-button--secondary', 'value' => 'Restore')
            )
        ),
        'row-disabled-callback' => function ($row) {
            return in_array(
                $row['status']['id'],
                [
                    Common\RefData::COMMUNITY_LICENCE_STATUS_EXPIRED,
                    Common\RefData::COMMUNITY_LICENCE_STATUS_VOID,
                    Common\RefData::COMMUNITY_LICENCE_STATUS_RETURNDED
                ]
            );
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
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'disableIfRowIsDisabled' => true,
            'data-attributes' => ['status']
        ),
    )
);
