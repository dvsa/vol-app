<?php

return array(
    'variables' => array(),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        ),
        'crud' => array(
            'formName' => 'fees',
            'actions' => array(
                'pay' => array('class' => 'primary', 'value' => 'Pay', 'requireRows' => true)
            )
        ),
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ),
        array(
            'title' => 'No',
            'sort' => 'invoiceNo',
            'name' => 'invoiceNo',
            'formatter' => function ($row, $column, $serviceLocator) {

                $url = '';

                $statusClass = 'green';
                switch ($row['feeStatus']['id']) {
                    case 'lfs_ot':
                        $statusClass = 'red';
                        break;
                    case 'lfs_pd':
                        $statusClass = 'green';
                        break;
                    case 'lfs_wr':
                        $statusClass = 'orange';
                        break;
                    case 'lfs_w':
                        $statusClass = 'green';
                        break;
                    case 'lfs_cn':
                        $statusClass = 'grey';
                        break;
                    default:
                        $statusClass = '';
                        break;
                }
                return '<a href="' . $url . '">' . $row['invoiceNo'] . '</a> <span class="status ' .
                        $statusClass . '">' . $row['feeStatus']['description'] . '</span>';
            },
        ),
        array(
            'title' => 'Description',
            'formatter' => function ($row, $column, $serviceLocator) {
                $router = $serviceLocator->get('router');
                $request = $serviceLocator->get('request');
                $routeMatch = $router->match($request);
                switch ($routeMatch->getMatchedRouteName()) {
                    case 'licence/fees':
                        $url = $this->generateUrl(
                            array('fee' => $row['id'], 'action' => 'edit-fee', 'controller' => 'LicenceController'),
                            'licence/fees/fee_action'
                        );
                        break;
                    case 'lva-application/fees':
                    default:
                        $url = $this->generateUrl(
                            array('fee' => $row['id'], 'action' => 'edit-fee', 'controller' => 'ApplicationController'),
                            'lva-application/fees/fee_action'
                        );
                }
                return '<a href="'
                    . $url
                    . '" class=js-modal-ajax>'
                    . $row['description']
                    . '</a>';
            },
            'sort' => 'description',
        ),
        array(
            'title' => 'Amount',
            'name' => 'amount',
            'sort' => 'amount'
        ),
        array(
            'title' => 'Created',
            'name' => 'invoicedDate',
            'formatter' => 'Date',
            'sort' => 'invoicedDate'
        ),
        array(
            'title' => 'Receipt No',
            'name' => 'receiptNo',
            'sort' => 'receiptNo'
        ),
        array(
            'title' => 'Received',
            'name' => 'receivedDate',
            'formatter' => 'Date',
            'sort' => 'receivedDate'
        ),
    )
);
