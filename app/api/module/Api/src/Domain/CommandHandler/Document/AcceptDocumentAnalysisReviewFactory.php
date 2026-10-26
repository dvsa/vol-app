<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\AnalysisResultNormaliser;
use Dvsa\Olcs\Api\Service\Idp\AnalysisReviewOutcome;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * AcceptDocumentAnalysisReview takes constructor arguments, so it cannot be mapped directly in
 * command-map.config.php - handlers mapped by class name are instantiated with no arguments.
 */
class AcceptDocumentAnalysisReviewFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): AcceptDocumentAnalysisReview
    {
        $instance = new AcceptDocumentAnalysisReview(
            $container->get(AnalysisResultNormaliser::class),
            $container->get(AnalysisReviewOutcome::class),
        );

        return $instance->__invoke($container, $requestedName, $options);
    }
}
