<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class RestrictedCountriesAnswerSaverFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return RestrictedCountriesAnswerSaver
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RestrictedCountriesAnswerSaver
    {
        $repoServiceManager = $container->get('RepositoryServiceManager');
        return new RestrictedCountriesAnswerSaver(
            $repoServiceManager->get('IrhpApplication'),
            $repoServiceManager->get('Country'),
            $container->get('QaCommonArrayCollectionFactory'),
            $container->get('QaNamedAnswerFetcher'),
            $container->get('QaGenericAnswerWriter'),
            $container->get('PermitsCommonStockBasedRestrictedCountryIdsProvider')
        );
    }
}
