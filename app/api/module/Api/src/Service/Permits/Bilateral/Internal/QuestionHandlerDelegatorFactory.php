<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Entity\Generic\Question;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class QuestionHandlerDelegatorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return QuestionHandlerDelegator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): QuestionHandlerDelegator
    {
        $questionHandlerDelegator = new QuestionHandlerDelegator(
            $container->get('QaContextFactory')
        );
        $mappings = [
            Question::QUESTION_ID_BILATERAL_PERMIT_USAGE => 'PermitUsage',
            Question::QUESTION_ID_BILATERAL_CABOTAGE_ONLY => 'CabotageOnly',
            Question::QUESTION_ID_BILATERAL_NUMBER_OF_PERMITS => 'NumberOfPermits',
            Question::QUESTION_ID_BILATERAL_STANDARD_AND_CABOTAGE => 'StandardAndCabotage',
            Question::QUESTION_ID_BILATERAL_THIRD_COUNTRY => 'ThirdCountry',
            Question::QUESTION_ID_BILATERAL_EMISSIONS_STANDARDS => 'EmissionsStandards',
            Question::QUESTION_ID_BILATERAL_NUMBER_OF_PERMITS_MOROCCO => 'NumberOfPermitsMorocco',
        ];
        foreach ($mappings as $questionId => $partialServiceName) {
            $serviceName = 'PermitsBilateralInternal' . $partialServiceName . 'QuestionHandler';
            $questionHandlerDelegator->registerQuestionHandler(
                $questionId,
                $container->get($serviceName)
            );
        }
        return $questionHandlerDelegator;
    }
}
