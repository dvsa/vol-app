<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class OptionListGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return OptionListGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): OptionListGenerator
    {
        $optionListGenerator = new OptionListGenerator(
            $container->get('QaOptionListFactory'),
            $container->get('QaOptionFactory')
        );
        $optionListGenerator->registerSource('refData', $container->get('QaRefDataOptionsSource'));
        $optionListGenerator->registerSource(
            'ecmtPermitUsageThreeOptionsRefData',
            $container->get('QaEcmtPermitUsageThreeOptionsRefDataOptionsSource')
        );
        $optionListGenerator->registerSource(
            'ecmtPermitUsageFourOptionsRefData',
            $container->get('QaEcmtPermitUsageFourOptionsRefDataOptionsSource')
        );
        $optionListGenerator->registerSource('repoQuery', $container->get('QaRepoQueryOptionsSource'));
        return $optionListGenerator;
    }
}
