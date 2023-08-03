<?php

namespace Olcs\Controller\Factory\Ebsr;

use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Ebsr\UploadsController;
use ZfcRbac\Service\AuthorizationService;

class UploadsControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return UploadsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UploadsController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $uploadHelper = $container->get(FileUploadHelperService::class);

        return new UploadsController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $uploadHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return UploadsController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): UploadsController
    {
        return $this->__invoke($serviceLocator, UploadsController::class);
    }
}
