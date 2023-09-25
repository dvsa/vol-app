<?php

use Common\Service\Table\Formatter\CaseLink;
use Common\Service\Table\Formatter\PiHearingStatus;
use Common\Service\Table\Formatter\PiReportName;
use Common\Service\Table\Formatter\PiReportRecord;
use Common\Service\Table\Formatter\VenueAddress;

return array(
    'variables' => array(
        'titleSingular' => 'Public Inquiry',
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
            'isNumeric' => true,
            'formatter' => function ($data) {
                return $this->callFormatter(
                    [
                        'formatter' => CaseLink::class,
                    ],
                    $data['pi']['case']
                );
            }
        ),
        array(
            'title' => 'Record',
            'formatter' => PiReportRecord::class
        ),
        array(
            'title' => 'Name',
            'formatter' => PiReportName::class
        ),
        array(
            'title' => 'PI Date & Time',
            'formatter' => function ($data) {
                return $this->callFormatter(
                    [
                        'name' => 'hearingDate',
                        'formatter' => \Common\Service\Table\Formatter\DateTime::class
                    ],
                    $data
                ).
                $this->callFormatter(
                    [
                        'formatter' => PiHearingStatus::class,
                    ],
                    $data
                );
            }
        ),
        array(
            'title' => 'Venue',
            'formatter' => VenueAddress::class
        ),
    )
);
