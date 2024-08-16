<?php

namespace Dvsa\Olcs\Api\Service\Template;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Class TwigRendererFactory
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class TwigRendererFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TwigRenderer
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TwigRenderer
    {
        return new TwigRenderer(
            $container->get('TemplateTwigEnvironment')
        );
    }
}
