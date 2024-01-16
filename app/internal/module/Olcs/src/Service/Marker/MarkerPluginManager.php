<?php

namespace Olcs\Service\Marker;

use Laminas\ServiceManager\AbstractPluginManager;
use Psr\Container\ContainerInterface;

/**
 * Class MarkerPluginManager
 */
class MarkerPluginManager extends AbstractPluginManager
{
    protected $instanceOf = MarkerInterface::class;

    public function __construct(ContainerInterface $container, array $config = [])
    {
        parent::__construct($container, $config);

        $this->addInitializer(new PartialHelperInitializer());
    }

    public function getMarkers(): array
    {
        return array_keys($this->factories);
    }
}
