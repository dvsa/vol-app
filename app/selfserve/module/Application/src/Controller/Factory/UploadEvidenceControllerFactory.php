<?php

namespace Dvsa\Olcs\Application\Controller\Factory;

use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Application\Controller\UploadEvidenceController;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
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
        $restrictionHelper = $container->get(RestrictionHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $uploadHelper = $container->get(FileUploadHelperService::class);

        return new UploadEvidenceController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $restrictionHelper,
            $stringHelper,
            $uploadHelper
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
