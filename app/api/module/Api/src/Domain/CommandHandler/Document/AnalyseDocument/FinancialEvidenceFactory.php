<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document\AnalyseDocument;

use Dvsa\Olcs\Api\Service\EventBridge\EventBridge;
use Dvsa\Olcs\Api\Service\Idp\AnalysisTokenGenerator;
use Dvsa\Olcs\Api\Service\Idp\ApplicantProfileBuilder;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * FinancialEvidence takes constructor arguments, so it cannot be mapped directly in
 * command-map.config.php - handlers mapped by class name are instantiated with no arguments.
 */
class FinancialEvidenceFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): FinancialEvidence
    {
        $instance = new FinancialEvidence(
            $container->get(EventBridge::class),
            $container->get(AnalysisTokenGenerator::class),
            $container->get(ApplicantProfileBuilder::class)
        );

        return $instance->__invoke($container, $requestedName, $options);
    }
}
