<?php

namespace Olcs\Service\Cookie;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class DeleteCookieNamesProviderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return DeleteCookieNamesProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : DeleteCookieNamesProvider
    {
        return $this->__invoke($serviceLocator, DeleteCookieNamesProvider::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return DeleteCookieNamesProvider
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : DeleteCookieNamesProvider
    {
        $deleteCookieNamesProvider = new DeleteCookieNamesProvider();
        $deleteCookieNamesProvider->registerCookieNamesProvider(
            Preferences::KEY_ANALYTICS,
            $container->get('CookieAnalyticsCookieNamesProvider')
        );
        $deleteCookieNamesProvider->registerCookieNamesProvider(
            Preferences::KEY_SETTINGS,
            $container->get('CookieSettingsCookieNamesProvider')
        );
        return $deleteCookieNamesProvider;
    }
}
