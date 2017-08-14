<?php

$variationNo = 1;
return array(
    'variables' => array(
        'title' => 'Registration history'
    ),
    'settings' => array(),
    'columns' => array(
        array(
            'title' => 'Reg No.',
            'formatter' => function ($data) {
                if (isset($data['id'])) {
                    if ((bool)$this->getVariable('isSearchPage') === true) {
                        return sprintf(
                            '<a href="%s">%s</a>',
                            $this->generateUrl(['busRegId' => $data['id']], 'search-bus/details', false),
                            $data['regNo']
                        );
                    } else {
                        return '<a href="' . $this->generateUrl(
                            array('action' => 'details', 'busRegId' => $data['id']),
                            'bus-registration/details',
                            false
                        ) . '">' . $data['regNo'] . '</a>';
                    }
                }
                return '';
            }
        ),
        array(
            'title' => 'Var No.',
            'name' => 'variationNo'
        ),
        array(
            'title' => 'Status',
            'formatter' => 'Translate',
            'name' => 'status->description',
        ),
        array(
            'title' => 'Application type',
            'formatter' => function ($data, $column, $sm) {
                if ($data['isTxcApp'] == 'Y') {
                    if ($data['ebsrRefresh'] == 'Y') {
                        return $sm->get('translator')->translate('EBSR Data Refresh');
                    } else {
                        return $sm->get('translator')->translate('EBSR');
                    }
                } else {
                    return $sm->get('translator')->translate('Manual');
                }
            }
        ),
        array(
            'title' => 'Date received',
            'formatter' => 'Date',
            'name' => 'receivedDate'
        ),
        array(
            'title' => 'Date effective',
            'formatter' => 'Date',
            'name' => 'effectiveDate'
        ),
        array(
            'title' => 'End date',
            'formatter' => 'Date',
            'name' => 'endDate'
        ),
        array(
            'title' => '&nbsp;',
            'width' => 'checkbox',
            'formatter' => function ($data) {
                if (isset($data['canDelete'])) {
                    return '<input type="radio" name="id" value="' . $data['id'] . '">';
                }
            },
        )
    )
);
