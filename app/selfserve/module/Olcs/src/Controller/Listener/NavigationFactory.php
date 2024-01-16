<?php

namespace Olcs\Controller\Listener;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;

class NavigationFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Navigation
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : Navigation
    {
        return new Navigation(
            $container->get('navigation'),
            $container->get('QuerySender'),
            $container->get(AuthorizationService::class)->getIdentity()
        );
    }
}
