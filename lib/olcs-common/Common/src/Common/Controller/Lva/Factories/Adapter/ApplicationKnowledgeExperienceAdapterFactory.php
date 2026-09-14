<?php

declare(strict_types=1);

namespace Common\Controller\Lva\Factories\Adapter;

use Common\Controller\Lva\Adapters\ApplicationKnowledgeExperienceAdapter;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final class ApplicationKnowledgeExperienceAdapterFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): ApplicationKnowledgeExperienceAdapter {
        return new ApplicationKnowledgeExperienceAdapter($container);
    }
}