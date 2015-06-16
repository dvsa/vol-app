<?php

namespace Olcs\Mvc\Controller\Plugin;

use Common\Service\Script\ScriptFactory;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Class Script
 * @package Olcs\Mvc\Controller\Plugin
 */
class Script extends AbstractPlugin
{
    /**
     * @var ScriptFactory
     */
    private $scriptFactory;

    /**
     * @param ScriptFactory $factory
     */
    public function __construct(ScriptFactory $factory)
    {
        $this->scriptFactory = $factory;
    }

    /**
     * @return $this
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * @param $scripts
     */
    public function addScripts($scripts)
    {
        $scripts = (array) $scripts;
        $this->scriptFactory->loadFiles($scripts);
    }
}
