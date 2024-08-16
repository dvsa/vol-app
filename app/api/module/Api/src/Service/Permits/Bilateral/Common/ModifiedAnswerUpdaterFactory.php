<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Common;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ModifiedAnswerUpdaterFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ModifiedAnswerUpdater
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ModifiedAnswerUpdater
    {
        return new ModifiedAnswerUpdater(
            $container->get('QaGenericAnswerWriter'),
            $container->get('QaApplicationAnswersClearer')
        );
    }
}
