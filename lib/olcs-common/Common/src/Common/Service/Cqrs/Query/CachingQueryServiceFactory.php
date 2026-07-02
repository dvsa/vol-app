<?php

namespace Common\Service\Cqrs\Query;

use Dvsa\Olcs\Transfer\Service\CacheEncryption as CacheEncryptionService;
use Exception;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CachingQueryServiceFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CachingQueryService
    {
        $config = $container->get('Config');

        if (!isset($config['query_cache'])) {
            throw new Exception('Query cache config key missing');
        }

        if (!isset($config['query_cache']['enabled'])) {
            throw new Exception('Query cache enabled/disabled config key missing');
        }

        $service = new CachingQueryService(
            $container->get(QueryService::class),
            $container->get(CacheEncryptionService::class),
            $container->get('TransferAnnotationBuilder'),
            $config['query_cache']['enabled'],
            $config['query_cache']['ttl']
        );

        $service->setLogger($container->get('Logger'));

        return $service;
    }
}
