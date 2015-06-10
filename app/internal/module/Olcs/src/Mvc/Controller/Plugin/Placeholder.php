<?php

namespace Olcs\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Helper\Placeholder as ViewPlaceholder;

class Placeholder extends AbstractPlugin
{
    private $placeholder;

    public function __construct(ViewPlaceholder $placeholder)
    {
        $this->placeholder = $placeholder;
    }

    public function __invoke()
    {
        return $this;
    }

    public function setPlaceholder($name, $value)
    {
        $this->placeholder->getContainer($name)->set($value);
    }
}
