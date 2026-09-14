<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ApplicationKnowledgeExperienceReviewServiceFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ) {
        return new ApplicationKnowledgeExperienceReviewService(
            $container->get(AbstractReviewServiceServices::class),
            $container->get('QueryHandlerManager')
        );
    }
}