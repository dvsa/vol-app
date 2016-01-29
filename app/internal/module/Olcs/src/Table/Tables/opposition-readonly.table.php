<?php

return array(
    'variables' => array(
        'title' => 'Opposition'
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'Case No.',
            'formatter' => function ($row) {
                return '<a href="' . $this->generateUrl(
                    array('case' => $row['case']['id'], 'tab' => 'overview'),
                    'case_opposition',
                    false
                ) . '">' . $row['case']['id'] . '</a>';
            }
        ),
        array(
            'title' => 'Case status',
            'name' => 'description',
            'formatter' => function ($row) {
                return ($row['case']['closedDate']) ? 'Closed' : 'Open';
            }
        ),
        array(
            'title' => 'Date received',
            'name' => 'raisedDate',
            'formatter' => 'Date',
            'sort' => 'raisedDate',
        ),
        array(
            'title' => 'Opposition type',
            'formatter' => 'RefData',
            'name' => 'oppositionType'
        ),
        array(
            'title' => 'Name',
            'formatter' => 'Name',
            'name' => 'opposer->contactDetails->person',
        ),
        array(
            'title' => 'Grounds',
            'formatter' => function ($data, $column) {
                $grounds = [];
                foreach ($data['grounds'] as $ground) {
                    $grounds[] = $ground['description'];
                }

                return implode(', ', $grounds);
            }
        ),
        array(
            'title' => 'App No.',
            'formatter' => function ($row) {
                return $row['case']['application']['id'];
            }
        ),
    )
);
