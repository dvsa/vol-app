<?php

namespace Common\Filter;

use Psr\Container\ContainerInterface;
use Laminas\Filter\Decompress;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;

/**
 * Class DecompressUploadToTmpFactory
 * @package Common\Filter
 */
class DecompressToTmpDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, callable $callback, array $options = null)
    {

        $config = $container->get('Config');
        $tmpRoot = ($config['tmpDirectory'] ?? sys_get_temp_dir());
        $filter = new Decompress('zip');

        $service = $callback();
        $service->setDecompressFilter($filter);
        $service->setTempRootDir($tmpRoot);
        $service->setFileSystem($container->get(\Common\Filesystem\Filesystem::class));

        return $service;
    }
}
