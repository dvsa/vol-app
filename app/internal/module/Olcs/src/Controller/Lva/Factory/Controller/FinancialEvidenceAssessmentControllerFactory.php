<?php

declare(strict_types=1);

namespace Olcs\Controller\Lva\Factory\Controller;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\Controller\Lva\AbstractFinancialEvidenceAssessmentController;
use Psr\Container\ContainerInterface;

/**
 * Builds the licence, application and variation financial evidence assessment controllers.
 *
 * They share one constructor, so one factory keyed on the requested class avoids three copies.
 */
class FinancialEvidenceAssessmentControllerFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): AbstractFinancialEvidenceAssessmentController {
        // Guard against the factory being wired to an unrelated class by mistake.
        if (!is_subclass_of($requestedName, AbstractFinancialEvidenceAssessmentController::class)) {
            throw new ServiceNotCreatedException(sprintf(
                '%s can only create subclasses of %s, %s given',
                self::class,
                AbstractFinancialEvidenceAssessmentController::class,
                $requestedName
            ));
        }

        return new $requestedName(
            $container->get(NiTextTranslation::class),
            $container->get(AuthorizationService::class),
            $container->get(StringHelperService::class),
            $container->get(RestrictionHelperService::class),
            $container->get(FlashMessengerHelperService::class),
            $container->get('navigation')
        );
    }
}
