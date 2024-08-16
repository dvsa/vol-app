<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\Custom;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EcmtRemovalNoOfPermitsGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return EcmtRemovalNoOfPermitsGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): EcmtRemovalNoOfPermitsGenerator
    {
        return new EcmtRemovalNoOfPermitsGenerator(
            $container->get('QaQuestionTextGenerator'),
            $container->get('RepositoryServiceManager')->get('FeeType')
        );
    }
}
