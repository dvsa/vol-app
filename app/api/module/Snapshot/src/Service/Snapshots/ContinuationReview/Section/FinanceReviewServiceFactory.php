<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FinanceReviewServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new FinanceReviewService(
            $container->get(AbstractReviewServiceServices::class),
            $container->get('FinancialStandingHelperService'),
            $container->get('RepositoryServiceManager')->get('Document')
        );
    }
}
