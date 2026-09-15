<?php

namespace Common\View\Helper;

use Laminas\View\HelperPluginManager as ViewHelperManager;

/**
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
trait PluginManagerAwareTrait
{
    /**
     * @var ViewHelperManager
     */
    protected $viewHelperManager;

    /**
     * @return $this
     */
    public function setViewHelperManager(ViewHelperManager $viewHelperManager)
    {
        $this->viewHelperManager = $viewHelperManager;
        return $this;
    }

    /**
     * @return ViewHelperManager
     */
    public function getViewHelperManager()
    {
        return $this->viewHelperManager;
    }
}
