<?php

namespace Dvsa\Olcs\Transfer\Service;

use Psr\Container\ContainerInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CacheEncryptionFactory implements FactoryInterface
{
    public const MISSING_CONFIG = 'Config is missing for cache encryption';

    /**
     * Invoke
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @throws \Exception
     * @return CacheEncryption
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CacheEncryption
    {
        $config = $container->get('Config');

        if (!isset($config['cache-encryption'])) {
            throw new \Exception(self::MISSING_CONFIG);
        }

        $cache = $container->get('default-cache');
        assert($cache instanceof StorageInterface);

        return new CacheEncryption(
            $cache,
            $config['cache-encryption']['secrets']['node'],
            $config['cache-encryption']['secrets']['shared'],
            $config['cache-encryption']['node_suffix']
        );
    }
}
