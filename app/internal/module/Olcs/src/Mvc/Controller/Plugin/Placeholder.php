<?php

namespace Olcs\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Helper\Placeholder as ViewPlaceholder;

/**
 * Class Placeholder
 * @package Olcs\Mvc\Controller\Plugin
 */
class Placeholder extends AbstractPlugin
{
    /**
     * @var ViewPlaceholder
     */
    private $placeholder;

    /**
     * @param ViewPlaceholder $placeholder
     */
    public function __construct(ViewPlaceholder $placeholder)
    {
        $this->placeholder = $placeholder;
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
     */
    public function setPlaceholder($name, $value)
    {
        $this->placeholder->getContainer($name)->set($value);
    }
}
