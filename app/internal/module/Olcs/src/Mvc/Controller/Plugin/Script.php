<?php

namespace Olcs\Mvc\Controller\Plugin;

use Common\Service\Script\ScriptFactory as CommonScriptFactory;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Class Script
 * @package Olcs\Mvc\Controller\Plugin
 */
class Script extends AbstractPlugin
{
    /**
     * @var CommonScriptFactory
     */
    private $scriptFactory;

    /**
     * @param CommonScriptFactory $factory
     */
    public function __construct(CommonScriptFactory $factory)
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

    /**
     * @param $scripts
     */
    public function appendScriptFiles($scripts)
    {
        $scripts = (array) $scripts;
        $this->scriptFactory->appendFiles($scripts);
    }
}
