<?php

namespace Olcs\Service\Qa;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class QuestionArrayProviderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return QuestionArrayProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : QuestionArrayProvider
    {
        return $this->__invoke($serviceLocator, QuestionArrayProvider::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return QuestionArrayProvider
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : QuestionArrayProvider
    {
        return new QuestionArrayProvider(
            $container->get('QaFormattedTranslateableTextParametersGenerator')
        );
    }
}
