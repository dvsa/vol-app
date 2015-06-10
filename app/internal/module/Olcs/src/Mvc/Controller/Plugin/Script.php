<?php

namespace Olcs\Mvc\Controller\Plugin;

use Common\Service\Script\ScriptFactory;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class Script extends AbstractPlugin
{
    private $scriptFactory;

    public function __construct(ScriptFactory $factory)
    {
        $this->scriptFactory = $factory;
    }

    public function __invoke()
    {
        return $this;
    }

    public function addScripts($scripts)
    {
        $scripts = (array) $scripts;
        $this->scriptFactory->loadFiles($scripts);
    }

    /**
     * @return \Zend\Mvc\Controller\AbstractActionController
     */
    public function getController()
    {
        return parent::getController();
    }
}