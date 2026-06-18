<?php

namespace Common\Controller\Plugin;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class CurrentUserFactory
 * @package Common\Controller\Plugin
 */
final class CurrentUserFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CurrentUser
    {
        return new CurrentUser($container->get(\LmcRbacMvc\Service\AuthorizationService::class));
    }
}
