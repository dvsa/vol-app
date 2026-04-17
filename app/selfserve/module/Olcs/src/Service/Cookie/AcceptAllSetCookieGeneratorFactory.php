<?php

namespace Olcs\Service\Cookie;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AcceptAllSetCookieGeneratorFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param mixed $requestedName
     * @param array|null $options
     * @return AcceptAllSetCookieGenerator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AcceptAllSetCookieGenerator
    {
        return new AcceptAllSetCookieGenerator(
            $container->get('CookiePreferencesSetCookieGenerator'),
            $container->get('CookiePreferencesFactory')
        );
    }
}
