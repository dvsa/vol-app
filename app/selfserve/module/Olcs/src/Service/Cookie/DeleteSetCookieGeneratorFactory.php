<?php

namespace Olcs\Service\Cookie;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DeleteSetCookieGeneratorFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return DeleteSetCookieGenerator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : DeleteSetCookieGenerator
    {
        return new DeleteSetCookieGenerator(
            $container->get('CookieSetCookieFactory'),
            $container->get('CookieCookieExpiryGenerator')
        );
    }
}
