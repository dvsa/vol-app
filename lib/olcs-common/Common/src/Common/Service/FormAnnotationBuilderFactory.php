<?php

namespace Common\Service;

use Laminas\Form\Annotation\AnnotationBuilder;
use Psr\Container\ContainerInterface;
use Laminas\Form\Factory;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FormAnnotationBuilderFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AnnotationBuilder
    {
        // set up a form factory which can use custom form elements
        $formElementManager = $container->get('FormElementManager');
        $formFactory = new Factory($formElementManager);

        // set up input filter factory to use custom validators + filters
        $inputFilterFactory = $formFactory->getInputFilterFactory();

        $inputFilterFactory->getDefaultValidatorChain()
            ->setPluginManager($container->get('ValidatorManager'));

        $inputFilterFactory->getDefaultFilterChain()
            ->setPluginManager($container->get('FilterManager'));

        // create service and set custom form factory
        $annotationBuilder = new AnnotationBuilder();
        $annotationBuilder->setFormFactory($formFactory);

        return $annotationBuilder;
    }
}
