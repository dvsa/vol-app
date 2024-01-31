<?php

namespace Olcs\Controller\Application\Processing;

use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Interop\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Service\Data\OperatingCentresForInspectionRequest;

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

        $operatingCentresForInspectionRequest = $container->get(OperatingCentresForInspectionRequest::class);

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
