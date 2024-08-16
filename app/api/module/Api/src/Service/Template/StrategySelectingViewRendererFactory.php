<?php

namespace Dvsa\Olcs\Api\Service\Template;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Class StrategySelectingViewRendererFactory
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class StrategySelectingViewRendererFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return StrategySelectingViewRenderer
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): StrategySelectingViewRenderer
    {
        return new StrategySelectingViewRenderer(
            $container->get('ViewRenderer'),
            $container->get('TemplateTwigRenderer'),
            $container->get('TemplateDatabaseTwigLoader')
        );
    }
}
