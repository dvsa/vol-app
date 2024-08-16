<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class NoOfPermitsAnswerClearerFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return NoOfPermitsAnswerClearer
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): NoOfPermitsAnswerClearer
    {
        return new NoOfPermitsAnswerClearer(
            $container->get('PermitsBilateralApplicationFeesClearer'),
            $container->get('RepositoryServiceManager')->get('IrhpPermitApplication')
        );
    }
}
