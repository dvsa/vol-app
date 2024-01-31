<?php

namespace Olcs\Controller\Factory\TransportManager\Details;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\TransportManagerHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\TransportManager\Details\TransportManagerDetailsResponsibilityController;

class TransportManagerDetailsResponsibilityControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return TransportManagerDetailsResponsibilityController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TransportManagerDetailsResponsibilityController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $navigation = $container->get('navigation');
        $transportManagerHelper = $container->get(TransportManagerHelperService::class);
        $transferAnnotationBuilder = $container->get(AnnotationBuilder::class);
        $commandService = $container->get(CommandService::class);
        $queryService = $container->get(QueryService::class);
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $uploadHelper = $container->get(FileUploadHelperService::class);

        return new TransportManagerDetailsResponsibilityController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $flashMessengerHelper,
            $translationHelper,
            $navigation,
            $transportManagerHelper,
            $transferAnnotationBuilder,
            $commandService,
            $queryService,
            $niTextTranslationUtil,
            $uploadHelper
        );
    }
}
