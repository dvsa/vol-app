<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Variation\UploadEvidenceController;
use LmcRbacMvc\Service\AuthorizationService;

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
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $uploadHelper = $container->get(FileUploadHelperService::class);

        return new UploadEvidenceController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $uploadHelper
        );
    }
}
