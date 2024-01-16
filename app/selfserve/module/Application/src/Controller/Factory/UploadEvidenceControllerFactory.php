<?php

namespace Dvsa\Olcs\Application\Controller\Factory;

use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Application\Controller\UploadEvidenceController;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
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
}
