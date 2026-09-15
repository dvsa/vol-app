<?php

namespace Common\Service\Table\Formatter;

use Laminas\Mvc\Controller\PluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;

class FormatterPluginManager extends PluginManager
{
    protected $instanceOf = FormatterPluginManagerInterface::class;

    protected $aliases = [
    ];

    protected $factories = [
    ];

    /**
     * Validate the plugin.
     * Throws exception if it is invalid.
     *
     * @param mixed $plugin
     * @throws InvalidServiceException If plugin is invalid.
     */
    #[\Override]
    public function validate($plugin): void
    {
        if (!($plugin instanceof $this->instanceOf)) {
            throw new InvalidServiceException(sprintf(
                'Plugin of type %s is invalid; must implement %s',
                (get_debug_type($plugin)),
                $this->instanceOf
            ));
        }
    }
}
