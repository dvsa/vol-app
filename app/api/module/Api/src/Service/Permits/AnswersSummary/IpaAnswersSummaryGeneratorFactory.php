<?php

namespace Dvsa\Olcs\Api\Service\Permits\AnswersSummary;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class IpaAnswersSummaryGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return IpaAnswersSummaryGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IpaAnswersSummaryGenerator
    {
        $answersSummaryGenerator = new IpaAnswersSummaryGenerator(
            $container->get('PermitsAnswersSummaryFactory'),
            $container->get('QaAnswersSummaryRowsAdder')
        );
        $answersSummaryGenerator->registerCustomRowsAdder(
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
            $container->get('PermitsBilateralIpaAnswersSummaryRowsAdder')
        );
        return $answersSummaryGenerator;
    }
}
