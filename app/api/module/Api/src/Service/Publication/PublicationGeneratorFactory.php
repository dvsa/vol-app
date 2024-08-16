<?php

namespace Dvsa\Olcs\Api\Service\Publication;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Dvsa\Olcs\Api\Service\Publication\Context\PluginManager as ContextPluginManager;
use Dvsa\Olcs\Api\Service\Publication\Process\PluginManager as ProcessPluginManager;
use Psr\Container\ContainerInterface;

/**
 * Class PublicationGeneratorFactory
 * @package Dvsa\Olcs\Api\Service\Publication
 */
class PublicationGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return PublicationGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PublicationGenerator
    {
        return new PublicationGenerator(
            $container->get('config')['publications'],
            $container->get(ContextPluginManager::class),
            $container->get(ProcessPluginManager::class)
        );
    }
}
