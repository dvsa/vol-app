<?php

namespace Olcs\Controller\Licence\Processing;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Interop\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Service\Data\OperatingCentresForInspectionRequest;

class LicenceProcessingInspectionRequestControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LicenceProcessingInspectionRequestController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        assert($translationHelper instanceof TranslationHelperService);

        $formHelper = $container->get(FormHelperService::class);
        assert($formHelper instanceof FormHelperService);

        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        assert($flashMessenger instanceof FlashMessengerHelperService);

        $navigation = $container->get('navigation');
        assert($navigation instanceof Navigation);

        $setUpOcListboxService = $container->get(OperatingCentresForInspectionRequest::class);
        assert($setUpOcListboxService instanceof OperatingCentresForInspectionRequest);

        $annotationBuilderService = $container->get(AnnotationBuilder::class);
        assert($annotationBuilderService instanceof AnnotationBuilder);

        return new LicenceProcessingInspectionRequestController(
            $translationHelper,
            $formHelper,
            $flashMessenger,
            $navigation,
            $setUpOcListboxService,
            $annotationBuilderService
        );
    }
    public function createService(ServiceLocatorInterface $serviceLocator): LicenceProcessingInspectionRequestController
    {
        $container = method_exists($serviceLocator, 'getServiceLocator') ? $serviceLocator->getServiceLocator() : $serviceLocator;

        return $this->__invoke(
            $container,
            LicenceProcessingInspectionRequestController::class
        );
    }
}
