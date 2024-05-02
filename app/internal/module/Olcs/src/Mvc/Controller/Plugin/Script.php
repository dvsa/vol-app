<?php

namespace Olcs\Mvc\Controller\Plugin;

use Common\Service\Script\ScriptFactory as CommonScriptFactory;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Class Script
 * @package Olcs\Mvc\Controller\Plugin
 */
class Script extends AbstractPlugin
{
    public function __construct(private CommonScriptFactory $scriptFactory)
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
