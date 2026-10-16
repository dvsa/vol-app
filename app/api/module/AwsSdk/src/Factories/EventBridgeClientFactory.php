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
     * Bounds exist because the only emit today happens inside the application submission
     * transaction. AnalyseDocument writes its row before emitting and leaves it PENDING for the
     * sweeper if the emit throws — but an unbounded call never throws, it just holds row locks
     * until something upstream gives up. These turn a hang into a catchable failure.
     */
    public const int CONNECT_TIMEOUT_SECONDS = 2;

    public const int TIMEOUT_SECONDS = 5;

    /** One retry, so the worst case stays bounded at roughly two attempts. */
    public const int RETRIES = 1;

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
        return new EventBridgeClient($this->clientArgs($container->get('config')));
    }

    /**
     * The SDK does not expose `http` through `getConfig()`, so the arguments are built here where
     * they can be asserted on directly rather than inferred from client internals.
     *
     * @param array $config Application config
     *
     * @return array Constructor arguments for the EventBridge client
     */
    public function clientArgs(array $config): array
    {
        $http = [
            'connect_timeout' => self::CONNECT_TIMEOUT_SECONDS,
            'timeout' => self::TIMEOUT_SECONDS,
        ];

        // Local development and CI have no egress proxy, and an empty value is not a valid one.
        if (!empty($config['awsOptions']['proxy'])) {
            $http['proxy'] = $config['awsOptions']['proxy'];
        }

        return [
            'region' => $config['awsOptions']['region'],
            'version' => $config['awsOptions']['version'],
            'http' => $http,
            'retries' => self::RETRIES,
        ];
    }
}
