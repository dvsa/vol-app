<?php

namespace Olcs\InputFilter;

use Zend\InputFilter\Input;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
