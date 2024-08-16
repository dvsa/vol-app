<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ExistingIrhpPermitApplicationHandlerFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ExistingIrhpPermitApplicationHandler
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ExistingIrhpPermitApplicationHandler
    {
        $repoServiceManager = $container->get('RepositoryServiceManager');
        return new ExistingIrhpPermitApplicationHandler(
            $repoServiceManager->get('IrhpPermitApplication'),
            $repoServiceManager->get('IrhpPermitStock'),
            $container->get('QaApplicationAnswersClearer'),
            $container->get('PermitsBilateralInternalQuestionHandlerDelegator')
        );
    }
}
