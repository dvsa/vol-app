<?php

namespace Olcs\InputFilter;

use Interop\Container\ContainerInterface;
use Laminas\InputFilter\Input;
use Laminas\ServiceManager\Factory\FactoryInterface;

class EbsrPackFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Input
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : Input
    {
        $validator = $container->get('ValidatorManager')->get('FileMimeType');
        $validator->setOptions(['mimeType' => 'application/zip']);
        $service = new Input();
        $service->getValidatorChain()->attach($validator);
        return $service;
    }
}
