<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ApplicationCountryRemoverFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ApplicationCountryRemover
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationCountryRemover
    {
        return new ApplicationCountryRemover(
            $container->get('CqrsCommandCreator'),
            $container->get('CommandHandlerManager')
        );
    }
}
