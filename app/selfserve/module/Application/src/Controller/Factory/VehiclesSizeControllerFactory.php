<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller\Factory;

use Common\FormService\FormServiceManager;
use Dvsa\Olcs\Application\Controller\VehiclesSizeController;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;

class VehiclesSizeControllerFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @param array|null $options
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): VehiclesSizeController
    {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formServiceManager = $container->get(FormServiceManager::class);

        return new VehiclesSizeController(
            $niTextTranslationUtil,
            $authService,
            $formServiceManager,
        );
    }
}
