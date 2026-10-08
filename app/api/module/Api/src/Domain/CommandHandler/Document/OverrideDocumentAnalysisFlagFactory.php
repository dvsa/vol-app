<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Service\Idp\AnalysisAnnotationOverlay;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\AnalysisResultNormaliser;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * OverrideDocumentAnalysisFlag takes constructor arguments, so it cannot be mapped directly in
 * command-map.config.php - handlers mapped by class name are instantiated with no arguments.
 */
class OverrideDocumentAnalysisFlagFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): OverrideDocumentAnalysisFlag
    {
        $instance = new OverrideDocumentAnalysisFlag(
            $container->get(AnalysisResultNormaliser::class),
            $container->get(AnalysisAnnotationOverlay::class),
        );

        return $instance->__invoke($container, $requestedName, $options);
    }
}

