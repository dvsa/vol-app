<?php

namespace Olcs\Form\Element;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory for EditorJs form element
 */
class EditorJsFactory implements FactoryInterface
{
    /**
     * Create EditorJs form element with HtmlConverter service
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): EditorJs
    {
        $htmlConverter = $container->get(\Olcs\Service\EditorJs\HtmlConverter::class);
        return new EditorJs($htmlConverter);
    }
}