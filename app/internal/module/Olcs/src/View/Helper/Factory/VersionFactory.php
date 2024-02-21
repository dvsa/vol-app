<?php

namespace Olcs\View\Helper\Factory;

use Psr\Container\ContainerInterface;
use Olcs\View\Helper\Version;
use Laminas\ServiceManager\Factory\FactoryInterface;

class VersionFactory implements FactoryInterface
{
    public const DEFAULT_VERSION = 'Not specified';

    /**
     * Get version number or default string from config
     *
     * @param array $config Config from service locator
     *
     * @return string
     */
    private function getVersion(array $config)
    {
        if (empty($config['application_version'])) {
            return self::DEFAULT_VERSION;
        }

        if (! is_string($config['application_version'])) {
            return self::DEFAULT_VERSION;
        }

        return $config['application_version'];
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Version
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Version
    {
        $config = $container->get('config');
        $version = $this->getVersion($config);
        $helper = new Version();
        $helper->setVersion($version);
        return $helper;
    }
}
