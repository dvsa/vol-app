<?php

namespace Common\Service\Qa\Custom\Common;

use Psr\Container\ContainerInterface;
use Laminas\Form\Factory as FormFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FileUploadFieldsetGeneratorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FileUploadFieldsetGenerator
    {
        return new FileUploadFieldsetGenerator(
            new FormFactory(),
            $container->get('FormAnnotationBuilder')
        );
    }
}
