<?php

namespace Olcs\Controller\Factory;

use Common\Service\Script\ScriptFactory;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\SplitScreenController;

class SplitScreenControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return SplitScreenController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SplitScreenController
    {
        $scriptFactory = $container->get(ScriptFactory::class);

        return new SplitScreenController(
            $scriptFactory
        );
    }
}
