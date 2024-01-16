<?php

namespace Olcs\Mvc;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CookieListenerFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new CookieListener(
            $container->get('CookieCookieReader'),
            $container->get('ViewHelperManager')->get('Placeholder')
        );
    }
}
