<?php

namespace Olcs\Service\Cookie;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DeleteCookieNamesProviderFactory implements FactoryInterface
{
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
