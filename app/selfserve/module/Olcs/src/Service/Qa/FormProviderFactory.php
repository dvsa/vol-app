<?php

namespace Olcs\Service\Qa;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Form\Factory as FormFactory;

class FormProviderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return FormProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : FormProvider
    {
        return $this->__invoke($serviceLocator, FormProvider::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return FormProvider
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : FormProvider
    {
        $config = $container->get('Config');
        return new FormProvider(
            $container->get('QaFormFactory'),
            $container->get('QaFieldsetPopulator'),
            new FormFactory(),
            $container->get('FormAnnotationBuilder'),
            $config['qa']['submit_options']
        );
    }
}
