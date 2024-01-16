<?php

namespace Olcs\Controller\Lva\Factory\Controller\Licence;

use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Review\LicenceConditionsUndertakingsReviewService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Licence\ConditionsUndertakingsController;
use LmcRbacMvc\Service\AuthorizationService;

class ConditionsUndertakingsControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ConditionsUndertakingsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConditionsUndertakingsController
    {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $licenceCUReviewService = $container->get(LicenceConditionsUndertakingsReviewService::class);
        $guidanceHelper = $container->get(GuidanceHelperService::class);

        return new ConditionsUndertakingsController(
            $niTextTranslationUtil,
            $authService,
            $licenceCUReviewService,
            $guidanceHelper
        );
    }
}
