<?php

namespace Olcs\Service\Data;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Soap\Client as SoapClient;
use Olcs\Logging\Log\Logger;

/**
 * Class NysiisFactory
 * @package Olcs\Service\Data
 */
class NysiisFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        $wsdl = file_get_contents($config['nysiis']['wsdl']['uri']);

        try {
            $soapClient = new SoapClient(
                $wsdl,
                $config['nysiis']['wsdl']['soap']['options']
            );
        } catch (\Exception $e) {
            Logger::debug(__FILE__ . 'Unable to create soap client: ' . $e->getMessage());
        }

        return new Nysiis($soapClient, $config);
    }
}
