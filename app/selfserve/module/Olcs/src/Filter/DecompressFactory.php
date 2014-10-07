<?php

namespace Olcs\Filter;

use Zend\Filter\Decompress;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DecompressFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $tmpRoot = (isset($config['tmpDirectory']) ? $config['tmpDirectory'] : sys_get_temp_dir());

        $tmpDir = tempnam($tmpRoot, 'zip');
        unlink($tmpDir);
        mkdir($tmpDir);

        $filter = new Decompress();
        $filter->setOptions(['target'=>$tmpDir]);

        return $filter;
    }
}
