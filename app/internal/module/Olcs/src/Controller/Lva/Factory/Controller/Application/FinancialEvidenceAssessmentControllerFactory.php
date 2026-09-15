<?php

namespace Olcs\Controller\Lva\Factory\Controller\Application;

use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Service\AuthorizationService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Application\FinancialEvidenceAssessmentController;
use Psr\Container\ContainerInterface;

class FinancialEvidenceAssessmentControllerFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): FinancialEvidenceAssessmentController
    {
        return new FinancialEvidenceAssessmentController(
            $container->get(NiTextTranslation::class),
            $container->get(AuthorizationService::class),
            $container->get(StringHelperService::class),
            $container->get(RestrictionHelperService::class)
        );
    }
}
