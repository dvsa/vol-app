<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class PermitUsageQuestionHandlerFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return PermitUsageQuestionHandler
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PermitUsageQuestionHandler
    {
        return new PermitUsageQuestionHandler(
            $container->get('PermitsBilateralInternalPermitUsageSelectionGenerator'),
            $container->get('PermitsBilateralCommonPermitUsageUpdater')
        );
    }
}
