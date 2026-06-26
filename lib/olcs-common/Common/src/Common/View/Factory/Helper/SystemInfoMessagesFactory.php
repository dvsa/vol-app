<?php

namespace Common\View\Factory\Helper;

use Common\View\Helper\SystemInfoMessages;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory for @see Common\View\Helper\SystemInfoMessages
 */
class SystemInfoMessagesFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SystemInfoMessages
    {
        /** @var \Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder $annotationBuilder */
        $annotationBuilder = $container->get('TransferAnnotationBuilder');
        /** @var \Common\Service\Cqrs\Query\CachingQueryService $queryService */
        $queryService = $container->get('QueryService');
        return new SystemInfoMessages($annotationBuilder, $queryService);
    }
}
