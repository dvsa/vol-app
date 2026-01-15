<?php

namespace Olcs\Controller\Factory\Document;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Document\HtmlEditorController;
use Common\Service\Helper\FormHelperService;

/**
 * Factory for HtmlEditorController
 */
class HtmlEditorControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ContainerInterface $container Container
     * @param string             $requestedName Service name
     * @param array|null         $options Options
     *
     * @return HtmlEditorController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $formHelper = $container->get(FormHelperService::class);
        
        return new HtmlEditorController($formHelper);
    }
}
