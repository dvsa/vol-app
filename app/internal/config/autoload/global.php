<?php

return array(
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
            'endpoint' => 'http://olcs-backend/',
            'apis' => array(
                'User' => 'user',
                'Person' => 'person',
            )
        )
    ),
    'application-name' => 'internal',
    /**
     * @todo Not sure if there is a better place to do this, but I essentially need to override the common controller
     * namespace to extend the behaviour
     */
    'controllers' => array(
        'invokables' => array(
            'Common\Controller\Application\VehicleSafety\SafetyController' =>
                'Olcs\Controller\Journey\Application\VehicleSafety\SafetyController',
        )
    ),
    'service_manager' => array(
        'factories' => array(
            'Logger' => function ($sm) { // CR: Remove this - and do it propertly!
                $log = new \Zend\Log\Logger();

                /**
                 * In development / integration - we log everything.
                 * In production, our logging
                 * is restricted to \Zend\Log\Logger::ERR and above.
                 *
                 * For logging priorities, see:
                 * @see http://www.php.net/manual/en/function.syslog.php#refsect1-function.syslog-parameters
                 */
                $filter = new \Zend\Log\Filter\Priority(LOG_DEBUG);

                // Log file
                $fileWriter = new \Zend\Log\Writer\Stream(sys_get_temp_dir() . '/olcs-application.log');
                $fileWriter->addFilter($filter);
                $log->addWriter($fileWriter);

                $nullWriter = new \Zend\Log\Writer\Null();
                $log->addWriter($nullWriter);

                // Log to sys log - useful if file logging is not working.
                /* $sysLogWriter = new \Zend\Log\Writer\Syslog();
                $sysLogWriter->addFilter($filter);
                $log->addWriter($sysLogWriter); */

                return $log;
            },
        ),
    ),
);
