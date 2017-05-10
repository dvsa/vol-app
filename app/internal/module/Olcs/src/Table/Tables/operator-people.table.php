<?php

return array(
    'variables' => array(
        'title' => 'People',
        'empty_message' => 'selfserve-app-subSection-your-business-people-other.table.empty-message',
        'required_label' => 'person',
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'action--primary'),
                'edit' => array('requireRows' => true),
                'delete' => array(
                    'label' => 'people_table_action.delete.label',
                    'class' => 'action--secondary',
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
            'formatter' => 'Name'
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
            'formatter' => 'Date',
        ),
        array(
            'title' => 'selfserve-app-subSection-your-business-people-columnPosition',
            'name' => 'position',
        ),
        array(
            'title' => 'Disqual',
            'formatter' => function ($row) {
                return sprintf(
                    '<a href="%s" class="js-modal-ajax">%s</a>',
                    $this->generateUrl(array('person' => $row['personId']), 'operator/disqualify_person'),
                    $row['disqualificationStatus']
                );
            }
        ),
        array(
            'name' => 'select',
            'width' => 'checkbox',
            'type' => 'Checkbox'
        )
    )
);
