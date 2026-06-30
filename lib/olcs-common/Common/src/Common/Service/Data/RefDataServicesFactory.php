<?php

namespace Common\Service\Data;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * RefDataServicesFactory
 */
class RefDataServicesFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RefDataServices
    {
        return new RefDataServices(
            $container->get(AbstractListDataServiceServices::class),
            $container->get('LanguagePreference')
        );
    }
}
