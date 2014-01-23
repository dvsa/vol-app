<?php
return array(
   'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => array(
                    'host'     => '127.0.0.1',
                    'port'     => '3306',
                    'user'     => 'olcs',
                    'password' => 'valtecholcs',
                    'dbname'   => 'olcs',
                )
            )
        )
    ),
    'service_api_mapping' => array(
        array(
            'endpoint' => 'http://olcspayment.dev/api/',
            'apis' => array(
                'Vosa\Payment\Token' => 'token',
                'Vosa\Payment\Db' => 'paymentdb',
                'Vosa\Payment\Card' => 'cardpayment',
            ),
        ),
        array(
            'endpoint' => 'http://olcs-db/api/',
            'apis' => array(
                'Olcs\Lookup' => 'lookup',
                'Olcs\Licence' => 'licence',
                'Olcs\Organisation' => 'organisation',
                'Olcs\Application' => 'application',
                'Olcs\Case' => 'case',
                'Olcs\Submission' => 'submission',
                'Olcs\User' => 'user',
                'Olcs\Payment' => 'olcs-payment',
                'Olcs\Application' => 'application',
                'Vosa\Service\Payment' => 'payment',
                'Olcs\CardPaymentTokenUsage' => 'card-payment-token-usage',
                'Olcs\Address' => 'address',
                'Olcs\Person' => 'person'
            ),
        ),
    ),
);
