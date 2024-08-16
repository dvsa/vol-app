<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender;

use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGeneratorServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\CommunityLicenceReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\CurrentDiscsReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\DeclarationReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\OperatorLicenceReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\LicenceDetailsService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\SignatureReviewService;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class GeneratorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new Generator(
            $container->get(AbstractGeneratorServices::class),
            $container->get(LicenceDetailsService::class),
            $container->get(CurrentDiscsReviewService::class),
            $container->get(OperatorLicenceReviewService::class),
            $container->get(CommunityLicenceReviewService::class),
            $container->get(DeclarationReviewService::class),
            $container->get(SignatureReviewService::class)
        );
    }
}
