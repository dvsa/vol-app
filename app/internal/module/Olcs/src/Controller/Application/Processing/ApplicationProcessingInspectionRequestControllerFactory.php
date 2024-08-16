<?php

namespace Olcs\Controller\Application\Processing;

use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Data\PluginManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Service\Data\OperatingCentresForInspectionRequest;
use Psr\Container\ContainerInterface;

class ApplicationProcessingInspectionRequestControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationProcessingInspectionRequestController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        assert($translationHelper instanceof TranslationHelperService);

        $formHelper = $container->get(FormHelperService::class);
        assert($formHelper instanceof FormHelperService);

        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        assert($flashMessengerHelper instanceof FlashMessengerHelperService);

        $navigation = $container->get('navigation');
        assert($navigation instanceof Navigation);

        $transferAnnotationBuilder = $container->get(TransferAnnotationBuilder::class);
        assert($transferAnnotationBuilder instanceof TransferAnnotationBuilder);

        $queryService = $container->get(CachingQueryService::class);
        assert($queryService instanceof CachingQueryService);

        $operatingCentresForInspectionRequest = $container->get(PluginManager::class)->get(OperatingCentresForInspectionRequest::class);

        return new ApplicationProcessingInspectionRequestController(
            $translationHelper,
            $formHelper,
            $flashMessengerHelper,
            $navigation,
            $transferAnnotationBuilder,
            $queryService,
            $operatingCentresForInspectionRequest
        );
    }
}
