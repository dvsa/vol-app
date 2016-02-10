<?php

namespace Olcs\Service\Data;

use Common\Service\Entity\Exceptions\UnexpectedResponseException;

/**
 * Class IrfoPsvAuthType
 */
class Nysiis
{
    /**
     * @var array
     */
    private $nysiisConfig;

    /**
     * @var \Zend\Soap\Client
     */
    private $soapClient;

    /**
     * Nysiis constructor.
     *
     * @param $soapClient
     * @param $config
     */
    public function __construct($soapClient, $config)
    {
        $this->soapClient = $soapClient;
        $this->nysiisConfig = $config;
    }
}
