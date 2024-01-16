<?php

namespace Olcs\Controller\Factory\Document;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Document\DocumentFinaliseController;
use Olcs\Service\Helper\WebDavJsonWebTokenGenerationService;

class DocumentFinaliseControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return DocumentFinaliseController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DocumentFinaliseController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $config = $container->get('Config');
        $flashMessangerHelper = $container->get(FlashMessengerHelperService::class);
        $webDavJsonWebTokenGenerationService = $container->get(WebDavJsonWebTokenGenerationService::class);

        return new DocumentFinaliseController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $config,
            $flashMessangerHelper,
            $webDavJsonWebTokenGenerationService
        );
    }
}
