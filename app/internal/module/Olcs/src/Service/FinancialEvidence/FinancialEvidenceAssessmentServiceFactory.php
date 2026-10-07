<?php

declare(strict_types=1);

namespace Olcs\Service\FinancialEvidence;

use Common\Service\Cqrs\Command\CommandSender;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final class FinancialEvidenceAssessmentServiceFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): FinancialEvidenceAssessmentService {
        /** @var CommandSender $commandSender */
        $commandSender = $container->get('CommandSender');

        return new FinancialEvidenceAssessmentService($commandSender);
    }
}

