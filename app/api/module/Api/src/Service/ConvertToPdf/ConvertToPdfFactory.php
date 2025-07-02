<?php

namespace Dvsa\Olcs\Api\Service\ConvertToPdf;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Http\Client as HttpClient;
use Dvsa\Olcs\Utils\Client\ClientAdapterLoggingWrapper;
use Psr\Container\ContainerInterface;

class ConvertToPdfFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ConvertToPdfInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConvertToPdfInterface
    {
        $config = $container->get('config');
        
        if (!isset($config['convert_to_pdf']['uri'])) {
            throw new \RuntimeException('Missing print service config[convert_to_pdf][uri]');
        }

        $type = $config['convert_to_pdf']['type'] ?? 'webservice';
        $uri = $config['convert_to_pdf']['uri'];
        $httpOptions = $config['convert_to_pdf']['options'] ?? [];

        if ($type === 'gotenberg') {
            $httpClient = new HttpClient($uri, $httpOptions);
            $wrapper = new ClientAdapterLoggingWrapper();
            $wrapper->wrapAdapter($httpClient);
            $wrapper->setShouldLogData(false);
            
            return new GotenbergClient($httpClient, $uri);
        }
        
        // Default to WebServiceClient for backward compatibility
        $httpClient = new HttpClient($uri, $httpOptions);
        $wrapper = new ClientAdapterLoggingWrapper();
        $wrapper->wrapAdapter($httpClient);
        $wrapper->setShouldLogData(false);
        
        return new WebServiceClient($httpClient);
    }
}