<?php

namespace Olcs\InputFilter;

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
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $validator = $serviceLocator->get('ValidatorManager')->get('FileMimeType');
        $validator->setOptions(['mimeType' => 'application/zip']);

        $service = new Input();
        $service->getValidatorChain()->attach($validator);

        return $service;
    }
}
