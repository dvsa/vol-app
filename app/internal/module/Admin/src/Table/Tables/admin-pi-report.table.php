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
                if (empty($data['pi']['case']['id'])) {
                    return '';
                }

                return sprintf(
                    '<a href="%s">%s</a>',
                    $this->generateUrl(
                        array(
                            'case' => $data['pi']['case']['id']
                        ),
                        'case',
                        false
                    ),
                    $data['pi']['case']['id']
                );
            }
        ),
        array(
            'title' => 'Record',
            'formatter' => function ($data) {
                if (!empty($data['pi']['case']['licence'])) {
                    return sprintf(
                        '<a href="%s">%s</a> (%s)',
                        $this->generateUrl(
                            array(
                                'licence' => $data['pi']['case']['licence']['id']
                            ),
                            'licence',
                            false
                        ),
                        $data['pi']['case']['licence']['licNo'],
                        $data['pi']['case']['licence']['status']['description']
                    );
                } elseif (!empty($data['pi']['case']['transportManager'])) {
                    return sprintf(
                        '<a href="%s">TM %s</a> (%s)',
                        $this->generateUrl(
                            array(
                                'transportManager' => $data['pi']['case']['transportManager']['id']
                            ),
                            'transport-manager',
                            false
                        ),
                        $data['pi']['case']['transportManager']['id'],
                        $data['pi']['case']['transportManager']['tmStatus']['description']
                    );
                }
                return '';
            }
        ),
        array(
            'title' => 'Name',
            'formatter' => function ($data) {
                if (!empty($data['pi']['case']['licence']['organisation'])) {
                    // display org linked to the licence
                    return $this->callFormatter(
                        [
                            'formatter' => 'OrganisationLink',
                        ],
                        $data['pi']['case']['licence']
                    );
                } elseif (!empty($data['pi']['case']['transportManager']['homeCd']['person'])) {
                    // display TM details
                    return $this->callFormatter(
                        [
                            'formatter' => 'Name',
                        ],
                        $data['pi']['case']['transportManager']['homeCd']['person']
                    );
                }
                return '';
            }
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
