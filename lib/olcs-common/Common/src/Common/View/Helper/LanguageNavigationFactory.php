<?php

namespace Common\View\Helper;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LanguageNavigationFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     *
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LanguageNavigation
    {
        $languagePref = $container->get('LanguagePreference');

        return new LanguageNavigation($languagePref);
    }
}
