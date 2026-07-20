<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Retrieval;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final class RetrievalPolicyResolverFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): RetrievalPolicyResolver
    {
        $config = $container->get('config');
        $policies = $config['retrieval']['policies'] ?? [];

        return new RetrievalPolicyResolver(is_array($policies) ? $policies : []);
    }
}
