<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class NumberOfPermitsQuestionHandlerFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return NumberOfPermitsQuestionHandler
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): NumberOfPermitsQuestionHandler
    {
        return new NumberOfPermitsQuestionHandler(
            $container->get('PermitsBilateralInternalPermitUsageSelectionGenerator'),
            $container->get('PermitsBilateralInternalBilateralRequiredGenerator'),
            $container->get('PermitsBilateralCommonNoOfPermitsConditionalUpdater')
        );
    }
}
