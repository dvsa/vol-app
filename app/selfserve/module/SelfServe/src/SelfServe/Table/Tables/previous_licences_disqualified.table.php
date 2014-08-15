<?php
$prefix = 'selfserve-app-subSection-previous-history-previous-licence-';

return array(
    'variables' => array(
        'title' => $prefix . 'tableHeader',
        'within_form' => true,
        'empty_message' => $prefix . 'tableEmptyMessage'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
            )
        )
    ),
    'attributes' => array(),
    'columns' => array(
        array(
            'width' => 'checkbox',
            'type' => 'Selector'
        ),
        array(
            'title' => $prefix . 'columnLicNo',
            'name' => 'licNo',
            'formatter' => function ($row) {
                return '<a href="' . $this->generateUrl(
                    array(
                        'id' => $row['id'],
                        'action' => 'table-licences-disqualified-edit'
                    ),
                    'Application/PreviousHistory/LicenceHistory'
                ) . '">' . $row['licNo'] . '</a>';
            }
        ),
        array(
            'title' => $prefix . 'columnHolderName',
            'name' => 'holderName',
        ),
        array(
            'title' => $prefix . 'columnDisqualificationDate',
            'name' => 'disqualificationDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => $prefix . 'columnDisqualificationLength',
            'name' => 'disqualificationLength',
        ),
    ),
    // Footer configuration
    'footer' => array(
    )
);
