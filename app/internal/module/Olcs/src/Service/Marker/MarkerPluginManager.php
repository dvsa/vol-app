<?php

namespace Olcs\Service\Marker;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception;

/**
 * Class MarkerPluginManager
 */
class MarkerPluginManager extends AbstractPluginManager
{
    public function __construct(\Laminas\ServiceManager\ConfigInterface $configuration = null)
    {
        parent::__construct($configuration);

        if ($configuration) {
            $configuration->configureServiceManager($this);
        }

        $this->addInitializer(
            new PartialHelperInitializer(),
            false
        );
    }

    /**
     * Validate the plugin
     *
     * Checks that the filter loaded is either a valid callback or an instance
     * of FilterInterface.
     *
     * @param  mixed $plugin
     * @return bool
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if (!($plugin instanceof \Olcs\Service\Marker\MarkerInterface)) {
            throw new \RuntimeException('Must implement MarkerInterface');
        }

        return true;
    }
}
