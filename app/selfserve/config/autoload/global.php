<?php

return array(
    'service_api_mapping' => array(
        array(
            'endpoints' => array(
                'payments' => 'http://olcspayment.dev/api/',
                'backend' => 'http://olcs-backend',
                'postcode' => 'http://postcode.cit.olcs.mgt.mtpdvsa/',
            ),
            'apis' => array(
                'Vosa\Payment\Token' => 'token',
                'Vosa\Payment\Db' => 'paymentdb',
                'Vosa\Payment\Card' => 'cardpayment',
            ),
        ),
        array(
            'endpoint' => 'http://olcs-backend/',
            'apis' => array(
                'User' => 'user',
                'Person' => 'person',
            )
        )
    ),
    'application-name' => 'selfserve'
);
