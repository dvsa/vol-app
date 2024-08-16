<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview;

use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGeneratorServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\SignatureReviewService;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class GeneratorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new Generator(
            $container->get(AbstractGeneratorServices::class),
            $container->get('SectionAccessService'),
            $container->get('Utils\NiTextTranslation'),
            $container->get(SignatureReviewService::class),
            $container
        );
    }
}
