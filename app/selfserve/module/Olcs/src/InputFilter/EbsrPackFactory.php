<?php

namespace Olcs\InputFilter;

use Interop\Container\ContainerInterface;
use Laminas\InputFilter\Input;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class EbsrPackFactory
 * @package Olcs\InputFilter
 */
class EbsrPackFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : Input
    {
        return $this->__invoke($serviceLocator, Input::class);
    }

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
