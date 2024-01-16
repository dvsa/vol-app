<?php

namespace Olcs\Controller\Factory\Ebsr;

use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Ebsr\UploadsController;
use LmcRbacMvc\Service\AuthorizationService;

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
}
