<?php

namespace Common\Service\Data;

use Laminas\ServiceManager\AbstractPluginManager;

/**
 * Class PluginManager
 * @package Common\Service\Data
 * @template-extends AbstractPluginManager<object>
 */
class PluginManager extends AbstractPluginManager
{
    protected $instanceOf;

    /**
     * @inheritdoc
     */
    public function __construct($configOrContainerInstance = null, array $v3config = [])
    {
        parent::__construct($configOrContainerInstance, $v3config);

        $this->addInitializer(
            new RestClientAwareInitializer()
        );
    }
}
