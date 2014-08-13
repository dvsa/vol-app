<?php

return array(
    'variables' => array(
        'title' => 'selfserve-app-subSection-previous-history-criminal-conviction-tableHeader',
        'within_form' => true,
        'empty_message' => 'selfserve-app-subSection-previous-history-criminal-conviction-tableEmptyMessage'
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
            'type' => 'Selector',
            'hideWhenDisabled' => true
        ),
        array(
            'title' => 'selfserve-app-subSection-previous-history-criminal-conviction-columnName',
            'name' => 'name',
            'formatter' => function ($row) {
                return '<a href="' . $this->generateUrl(
                    array(
                        'id' => $row['id'],
                        'action' => 'edit'
                    ),
                    'Application/PreviousHistory/ConvictionsPenalties'
                ) . '">' . $row['name'] . '</a>';
            }
        ),
        array(
            'title' => 'selfserve-app-subSection-previous-history-criminal-conviction-columnDate',
            'name' => 'convictionDate',
            'formatter' => 'Date',
        ),
        array(
            'title' => 'selfserve-app-subSection-previous-history-criminal-conviction-columnOffence',
            'name' => 'categoryText',
        ),
        array(
            'title' => 'selfserve-app-subSection-previous-history-criminal-conviction-columnNameOfCourt',
            'name' => 'courtFpn',
        ),
        array(
            'title' => 'selfserve-app-subSection-previous-history-criminal-conviction-columnPenalty',
            'name' => 'penalty',
        )
    ),
    // Footer configuration
    'footer' => array(
    )
);
