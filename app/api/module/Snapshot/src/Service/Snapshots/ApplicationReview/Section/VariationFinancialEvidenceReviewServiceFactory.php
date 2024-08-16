<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class VariationFinancialEvidenceReviewServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new VariationFinancialEvidenceReviewService(
            $container->get(AbstractReviewServiceServices::class),
            $container->get('Review\ApplicationFinancialEvidence')
        );
    }
}
