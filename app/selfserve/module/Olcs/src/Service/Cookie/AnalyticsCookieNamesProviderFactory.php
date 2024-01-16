<?php

namespace Olcs\Service\Cookie;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AnalyticsCookieNamesProviderFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param mixed $requestedName
     * @param array|null $options
     * @return AnalyticsCookieNamesProvider
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : AnalyticsCookieNamesProvider
    {
        $config = $container->get('Config');
        return new AnalyticsCookieNamesProvider($config['google-ga-domain']);
    }
}
