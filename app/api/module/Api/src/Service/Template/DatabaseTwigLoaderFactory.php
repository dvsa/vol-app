<?php

namespace Dvsa\Olcs\Api\Service\Template;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DatabaseTwigLoaderFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return DatabaseTwigLoader
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DatabaseTwigLoader
    {
        return new DatabaseTwigLoader(
            $container->get('TemplateDatabaseTemplateFetcher')
        );
    }
}
