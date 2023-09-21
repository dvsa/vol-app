<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\Name;
use Common\Service\Table\Formatter\RefData;

return array(
    'variables' => array(
        'titleSingular' => 'Opposition',
        'title' => 'Opposition'
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'Case No.',
            'isNumeric' => true,
            'formatter' => function ($row) {
                return '<a class="govuk-link" href="' . $this->generateUrl(
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
            'formatter' => Date::class,
            'sort' => 'raisedDate',
        ),
        array(
            'title' => 'Opposition type',
            'formatter' => RefData::class,
            'name' => 'oppositionType'
        ),
        array(
            'title' => 'Name',
            'formatter' => Name::class,
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
            'isNumeric' => true,
            'formatter' => function ($row) {
                return $row['case']['application']['id'];
            }
        ),
    )
);
