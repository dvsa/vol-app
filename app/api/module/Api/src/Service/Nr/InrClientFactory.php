<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Nr;

use Dvsa\Olcs\Api\Service\AccessToken\Provider;
use Dvsa\Olcs\Utils\Client\ClientAdapterLoggingWrapper;
use Laminas\Http\Request;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Http\Client as RestClient;
use Psr\Container\ContainerInterface;

class InrClientFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return InrClient
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): InrClient
    {
        $config = $container->get('config');
        if (!isset($config['nr']['inr_service'])) {
            throw new \RuntimeException('Missing INR service config');
        }

        $path = $options['path'] ?? '';

        /** @var Provider $tokenProvider */
        $tokenProvider = $container->build(Provider::class, $config['nr']['inr_service']['oauth2']);
        $headers = ['Authorization' => 'Bearer ' .  $tokenProvider->getToken()];

        $httpClient = new RestClient($config['nr']['inr_service']['uri'] . $path);
        $httpClient->setAdapter($config['nr']['inr_service']['adapter']);
        $httpClient->getAdapter()->setOptions($config['nr']['inr_service']['options']);
        $httpClient->setHeaders($headers);
        $httpClient->setMethod(Request::METHOD_POST);
        $wrapper = new ClientAdapterLoggingWrapper();
        $wrapper->wrapAdapter($httpClient);
        return new InrClient($httpClient);
    }
}
