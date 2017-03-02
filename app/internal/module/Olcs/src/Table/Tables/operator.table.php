<?php

return array(
    'variables' => array(
        'title' => 'Result list'
    ),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        ),
        'crud' => array(
            'actions' => array(
                'createOperator' => array('class' => 'action--primary', 'value' => 'Create operator'),
                'createTransportManager' => array('class' => 'action--secondary', 'value' => 'Create transport manager')
            )
        ),
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Lic no/status',
            'sort' => 'licNo',
            'formatter' => function ($row) {
                return '<a href="' . $this->generateUrl(
                    array('licence' => $row['licenceId']),
                    'licence'
                ) . '">' . $row['licNo'] . '</a><br/>' . $row['status'];
            },
        ),
        array(
            'title' => 'App ID/status',
            'format' => '{{appNumber}}<br/>{{appStatus}}',
            'sort' => 'appId'
        ),
        array(
            'title' => 'Op/trading name',
            'formatter' => function ($data) {
                return '<a href="' . $this->generateUrl(
                    array('operator' => $data['organisation_id']),
                    'operator/business-details'
                ) . '">' . $data['name'] . '</a><br/>' . $data['status'];
            },
            'sort' => 'operatorName'
        ),
        array(
            'title' => 'Entity / Lic Type',
            'format' => '{{organisation_type}} / {{licence_type}}',
        ),
        array(
            'title' => 'Last act CN/Date',
            'name' => 'last_updated_on',
            'formatter' => 'Date',
            'sort' => 'lastActionDate'
        ),
        array(
            'title' => 'Correspondence address',
            'formatter' => function ($data) {
                $parts = array();
                foreach (array('address_line1', 'address_line2', 'address_line3', 'postcode') as $item) {
                    if (!empty($data[$item])) {
                        $parts[] = $data[$item];
                    }
                }

                return implode(', ', $parts);
            },
            'sort' => 'correspondenceAddress'
        ),
        array(
            'title' => 'Cases',
            'formatter' => function ($data) {
                if (isset($data['caseCount']) && (int) $data['caseCount'] > 0) {
                    return '<a href="' . $this->generateUrl(
                        array('licence' => $data['licenceId']),
                        'licence/cases',
                        false
                    ) . '">' . $data['caseCount'] . '</a>';
                } else {
                    return '<a href="' . $this->generateUrl(
                        array('licence' => $data['licenceId'], 'action' => 'add'),
                        'case'
                    ) . '">[Add Case]</a>';
                }
            }
        ),
        array(
            'title' => 'MLH',
            'format' => '[MLH]'
        ),
        array(
            'title' => 'Info',
            'formatter' => 'Date',
            'formatter' => function ($data, $column, $sm) {
                $translator = $sm->get('translator');
                $string = '<span class="tooltip">';
                $string .= !empty($data['startDate']) ?
                        ucfirst($translator->translate('start')) . ': ' .
                        $data['startDate'] . '<br />' : '';
                $string .= !empty($data['reviewDate']) ?
                        ucfirst($translator->translate('review')) . ': ' .
                        $data['reviewDate'] . '<br />' : '';
                $string .= !empty($data['endDate']) ?
                        ucfirst($translator->translate('end')) . ': ' .
                        $data['endDate'] . '<br />' : '';
                $string .= !empty($data['fabsReference']) ?
                        ucfirst($translator->translate('fabs-reference')) . ': ' .
                        $data['fabsReference'] . '<br />' : '';
                $string .= '</span>';

                if ($string == '<span class="tooltip"></span>') {
                    $string = '';
                }
                return $string;
            }
        )
    )
);
