<?php

namespace Olcs\Service\Qa;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

use Laminas\Form\Factory as FormFactory;

class FormProviderFactory implements FactoryInterface
{
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
