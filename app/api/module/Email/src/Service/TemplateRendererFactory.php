<?php

namespace Dvsa\Olcs\Email\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Class TemplateRenderer
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TemplateRendererFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TemplateRenderer
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TemplateRenderer
    {
        $templateRenderer = new TemplateRenderer();
        $templateRenderer->setViewRenderer($container->get('TemplateStrategySelectingViewRenderer'));
        return $templateRenderer;
    }
}
