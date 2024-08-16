<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Radio;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class RadioAnswerSummaryProviderFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return RadioAnswerSummaryProvider
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RadioAnswerSummaryProvider
    {
        return new RadioAnswerSummaryProvider(
            $container->get('QaOptionListGenerator')
        );
    }
}
