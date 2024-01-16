<?php

namespace Olcs\Service\Qa;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ViewGeneratorProviderFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ViewGeneratorProvider
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : ViewGeneratorProvider
    {
        $viewGeneratorProvider = new ViewGeneratorProvider();
        $viewGeneratorProvider->registerViewGenerator(
            'permits/application/question',
            $container->get('QaIrhpApplicationViewGenerator')
        );
        $viewGeneratorProvider->registerViewGenerator(
            'permits/application/ipa/question',
            $container->get('QaIrhpPermitApplicationViewGenerator')
        );
        return $viewGeneratorProvider;
    }
}
