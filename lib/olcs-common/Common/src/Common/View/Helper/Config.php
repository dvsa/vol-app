<?php

namespace Common\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Class return Config to view
 */
class Config extends AbstractHelper
{
    /**
     * Create service instance
     *
     *
     * @return Config
     */
    public function __construct(private array $config)
    {
    }

    /**
     * Return config
     *
     * @return array
     */
    public function __invoke()
    {
        return $this->config;
    }
}
