<?php

namespace Permits\Data\Mapper;

use Laminas\ServiceManager\Config;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class MapperManagerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return MapperManager
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');

        // Ensuring the mappers key exists in the configuration
        if (!isset($config['mappers'])) {
            throw new \Exception('Mappers configuration not found');
        }

        $configObject = new Config($config['mappers']);

        return new MapperManager($configObject);
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, MapperManager::class);
    }
}
