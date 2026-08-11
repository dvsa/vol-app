<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Retrieval;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final class RetrievalLinkCreatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): RetrievalLinkCreator
    {
        return new RetrievalLinkCreator(
            $container->get('RepositoryServiceManager'),
            $container->get(TokenGenerator::class),
            $container->get(RetrievalPolicyResolver::class),
        );
    }
}
