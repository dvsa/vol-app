<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Lva\AbstractVehiclesSizeController;
use Common\FormService\FormServiceManager;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Psr\Container\ContainerInterface;

class VehiclesSizeController extends AbstractVehiclesSizeController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location  = 'external';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): VehiclesSizeController
    {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formServiceManager = $container->get(FormServiceManager::class);

        return new self(
            $niTextTranslationUtil,
            $authService,
            $formServiceManager,
        );
    }
}
