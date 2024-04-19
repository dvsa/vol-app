<?php

namespace Olcs\Mvc\Controller\Plugin;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Laminas\View\Helper\Placeholder as ViewPlaceholder;

/**
 * Class Placeholder
 * @package Olcs\Mvc\Controller\Plugin
 */
class Placeholder extends AbstractPlugin
{
    public function __construct(private ViewPlaceholder $placeholder)
    {
    }

    /**
     * @return $this
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * @param $name
     * @param $value
     *
     * @psalm-param 'key' $name
     * @psalm-param 'value' $value
     */
    public function setPlaceholder(string $name, string $value): void
    {
        $this->placeholder->getContainer($name)->set($value);
    }
}
