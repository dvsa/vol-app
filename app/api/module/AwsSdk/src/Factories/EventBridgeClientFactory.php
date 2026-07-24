<?php

namespace Dvsa\Olcs\AwsSdk\Factories;

use Aws\EventBridge\EventBridgeClient;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Class EventBridgeClientFactory
 *
 * @package Dvsa\Olcs\AwsSdk\Factories
 */
class EventBridgeClientFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return EventBridgeClient
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): EventBridgeClient
    {
        $config = $container->get('config');
        return new EventBridgeClient([
            'region' => $config['awsOptions']['region'],
            'version' => $config['awsOptions']['version'],
        ]);
    }
}
