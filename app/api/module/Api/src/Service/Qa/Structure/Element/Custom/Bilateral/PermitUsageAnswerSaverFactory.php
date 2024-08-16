<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class PermitUsageAnswerSaverFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return PermitUsageAnswerSaver
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PermitUsageAnswerSaver
    {
        return new PermitUsageAnswerSaver(
            $container->get('QaGenericAnswerFetcher'),
            $container->get('PermitsBilateralCommonPermitUsageUpdater')
        );
    }
}
