<?php

namespace Olcs\Service\Marker;

use Dvsa\Olcs\Utils\Traits\PluginManagerTrait;
use Laminas\ServiceManager\AbstractPluginManager;

/**
 * Class MarkerPluginManager
 */
class MarkerPluginManager extends AbstractPluginManager
{
    use PluginManagerTrait;

    protected $instanceOf = MarkerInterface::class;

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
}
