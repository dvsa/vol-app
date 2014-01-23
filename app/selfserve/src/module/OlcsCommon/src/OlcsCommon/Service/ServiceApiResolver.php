<?php

namespace OlcsCommon\Service;

use \Zend\ServiceManager\FactoryInterface;
use \Zend\ServiceManager\ServiceLocatorInterface;
use \OlcsCommon\Utility\ResolveApi;

/**
 * Description of ServiceApiResolver
 *
 * @author Pelle Wessman <pelle.wessman@valtech.se>
 */
class ServiceApiResolver implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        $serviceApiConfig = empty($config['service_api_mapping']) ? array() : $config['service_api_mapping'];
        $serviceApiMapping = array();

        foreach ($serviceApiConfig as $endpoint) {
            $apis = empty($endpoint['apis']) ? array() : $endpoint['apis'];
            foreach ($apis as $api => $path) {
                $serviceApiMapping[$api] = array(
                    'baseUrl' => $endpoint['endpoint'],
                    'path' => $path,
                );
            }
        }

        return new ResolveApi($serviceApiMapping);
    }
}
