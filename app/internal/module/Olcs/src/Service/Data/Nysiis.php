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
     * Nysiis constructor. Initiates a SOAP client and configuration
     *
     * @param $soapClient
     * @param $config
     */
    public function __construct($soapClient, $config)
    {
        $this->soapClient = $soapClient;
        $this->nysiisConfig = $config;
    }

    /**
     * Make SOAP request to NYSIIS to extract the firstName and familyName.
     * @to-do When WSDL/SOAP server known, do the connection proper.
     * For now just return the params
     *
     * @param $params
     * @return mixed
     */
    public function getNysiisSearchKeys($params)
    {
        return $params;
        //$result = $this->soapClient->GetNYSIISSearchKeys($params['nysiisForename'], $params['nysiisFamilyname']);
    }

    /**
     * @return array
     */
    public function getNysiisConfig()
    {
        return $this->nysiisConfig;
    }

    /**
     * @return \Zend\Soap\Client
     */
    public function getSoapClient()
    {
        return $this->soapClient;
    }
}
