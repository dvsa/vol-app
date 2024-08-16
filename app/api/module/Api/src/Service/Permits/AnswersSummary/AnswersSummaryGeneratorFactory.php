<?php

namespace Dvsa\Olcs\Api\Service\Permits\AnswersSummary;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AnswersSummaryGeneratorFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return AnswersSummaryGenerator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AnswersSummaryGenerator
    {
        $answersSummaryGenerator = new AnswersSummaryGenerator(
            $container->get('PermitsAnswersSummaryFactory'),
            $container->get('PermitsHeaderAnswersSummaryRowsAdder'),
            $container->get('QaAnswersSummaryRowsAdder')
        );
        $answersSummaryGenerator->registerCustomRowsAdder(
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
            $container->get('PermitsBilateralAnswersSummaryRowsAdder')
        );
        $answersSummaryGenerator->registerCustomRowsAdder(
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL,
            $container->get('PermitsMultilateralAnswersSummaryRowsAdder')
        );
        return $answersSummaryGenerator;
    }
}
