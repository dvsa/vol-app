<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataServiceServices;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TeamFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return Team
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Team
    {
        return new Team(
            $container->get(AbstractDataServiceServices::class),
            $container->get('Helper\FlashMessenger')
        );
    }
}
