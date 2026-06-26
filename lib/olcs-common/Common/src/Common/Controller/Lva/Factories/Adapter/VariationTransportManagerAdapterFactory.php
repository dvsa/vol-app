<?php

namespace Common\Controller\Lva\Factories\Adapter;

use Common\Controller\Lva\Adapters\VariationTransportManagerAdapter;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\CachingQueryService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class VariationTransportManagerAdapterFactory implements FactoryInterface
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): VariationTransportManagerAdapter
    {
        $transferAnnotationBuilder = $container->get(AnnotationBuilder::class);
        assert($transferAnnotationBuilder instanceof AnnotationBuilder);

        $queryService = $container->get(CachingQueryService::class);
        assert($queryService instanceof CachingQueryService);

        $commandService = $container->get(CommandService::class);
        assert($commandService instanceof CommandService);

        return new VariationTransportManagerAdapter($transferAnnotationBuilder, $queryService, $commandService, $container);
    }
}
