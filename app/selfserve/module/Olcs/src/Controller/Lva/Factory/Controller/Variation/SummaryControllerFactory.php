<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Variation\SummaryController;
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

        return new SummaryController(
            $niTextTranslationUtil,
            $authService
        );
    }
}
