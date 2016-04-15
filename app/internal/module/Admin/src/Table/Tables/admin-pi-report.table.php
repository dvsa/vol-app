<?php

return array(
    'variables' => array(
        'title' => 'Public Inquiries'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 25,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Case Id',
            'formatter' => function ($data) {
                return $this->callFormatter(
                    [
                        'formatter' => 'CaseLink',
                    ],
                    $data['pi']['case']
                );
            }
        ),
        array(
            'title' => 'Record',
            'formatter' => 'PiReportRecord'
        ),
        array(
            'title' => 'Name',
            'formatter' => 'PiReportName'
        ),
        array(
            'title' => 'PI Date & Time',
            'formatter' => function ($data) {
                return $this->callFormatter(
                    [
                        'name' => 'hearingDate',
                        'formatter' => 'DateTime',
                    ],
                    $data
                ).
                $this->callFormatter(
                    [
                        'formatter' => 'PiHearingStatus',
                    ],
                    $data
                );
            }
        ),
        array(
            'title' => 'Venue',
            'formatter' => 'VenueAddress'
        ),
    )
);
