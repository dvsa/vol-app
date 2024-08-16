<?php

namespace Dvsa\Olcs\Application\Controller\Factory;

use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Application\Controller\SummaryController;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;

class SummaryControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SummaryController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SummaryController
    {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $restrictionHelper = $container->get(RestrictionHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);

        return new SummaryController(
            $niTextTranslationUtil,
            $authService,
            $restrictionHelper,
            $stringHelper
        );
    }
}
