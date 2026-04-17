<?php

namespace Admin\Controller;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Helper\Placeholder;

class IndexControllerFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IndexController
    {
        $placeholder = $container->get('ViewHelperManager')->get(Placeholder::class);

        return new IndexController(
            $placeholder
        );
    }
}
