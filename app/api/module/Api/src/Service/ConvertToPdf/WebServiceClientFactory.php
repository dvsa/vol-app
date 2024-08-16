<?php

namespace Dvsa\Olcs\Api\Service\ConvertToPdf;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Http\Client as HttpClient;
use Dvsa\Olcs\Utils\Client\ClientAdapterLoggingWrapper;
use Psr\Container\ContainerInterface;

/**
 * Class WebServiceClientFactory
 */
class WebServiceClientFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return WebServiceClient
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): WebServiceClient
    {
        $config = $container->get('config');
        if (!isset($config['convert_to_pdf']['uri'])) {
            throw new \RuntimeException('Missing print service config[convert_to_pdf][uri]');
        }
        $options = $config['convert_to_pdf']['options'] ?? [];
        $httpClient = new HttpClient($config['convert_to_pdf']['uri'], $options);
        $wrapper = new ClientAdapterLoggingWrapper();
        $wrapper->wrapAdapter($httpClient);
        $wrapper->setShouldLogData(false);
        return new WebServiceClient($httpClient);
    }
}
