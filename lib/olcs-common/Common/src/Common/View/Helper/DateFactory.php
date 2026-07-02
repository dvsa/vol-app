<?php

namespace Common\View\Helper;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DateFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     *
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Date
    {
        $viewHelperManager = $container->get('ViewHelperManager');
        $translator = $viewHelperManager->get('translate');

        return new Date($translator);
    }
}
