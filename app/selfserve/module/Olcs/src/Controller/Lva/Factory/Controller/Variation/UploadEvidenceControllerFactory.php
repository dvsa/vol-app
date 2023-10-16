<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Variation\UploadEvidenceController;
use ZfcRbac\Service\AuthorizationService;

class UploadEvidenceControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return UploadEvidenceController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UploadEvidenceController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);

        return new UploadEvidenceController(
            $niTextTranslationUtil,
            $authService,
            $formHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return UploadEvidenceController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): UploadEvidenceController
    {
        return $this->__invoke($serviceLocator, UploadEvidenceController::class);
    }
}
