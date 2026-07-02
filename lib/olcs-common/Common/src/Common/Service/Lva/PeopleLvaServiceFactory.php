<?php

namespace Common\Service\Lva;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PeopleLvaServiceFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PeopleLvaService
    {
        return new PeopleLvaService(
            $container->get('Helper\Form')
        );
    }
}
