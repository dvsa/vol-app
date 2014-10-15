<?php

namespace Olcs\Filter;

use Olcs\Filesystem\Filesystem;
use Zend\Filter\Decompress;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DecompressUploadToTmpFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->getServiceLocator()->get('Config');
        $tmpRoot = (isset($config['tmpDirectory']) ? $config['tmpDirectory'] : sys_get_temp_dir());
        $filter = new Decompress('zip');

        $service = new DecompressUploadToTmp();
        $service->setDecompressFilter($filter);
        $service->setTempRootDir($tmpRoot);
        $service->setFileSystem(new Filesystem());


        return $service;
    }
}
