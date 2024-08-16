<?php

namespace Dvsa\Olcs\Api\Service\Template;

use Twig\Environment;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class TwigEnvironmentFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new Environment(
            $container->get('TemplateDatabaseTwigLoader'),
            [
                'strict_variables' => true,
                'cache' => sys_get_temp_dir(),
                'auto_reload' => true,
            ]
        );
    }
}
