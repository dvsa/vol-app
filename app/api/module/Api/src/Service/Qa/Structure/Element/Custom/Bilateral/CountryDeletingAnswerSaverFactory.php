<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CountryDeletingAnswerSaverFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return CountryDeletingAnswerSaver
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CountryDeletingAnswerSaver
    {
        return new CountryDeletingAnswerSaver(
            $container->get('QaGenericAnswerFetcher'),
            $container->get('QaGenericAnswerWriter'),
            $container->get('QaBilateralClientReturnCodeHandler')
        );
    }
}
