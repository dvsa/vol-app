<?php

declare(strict_types=1);

namespace CommonTest\Common\Controller\Plugin;

use Laminas\Mvc\Controller\AbstractActionController as LaminasAbstractActionController;
use Laminas\View\Helper\Placeholder;
use Laminas\View\Model\ViewModel;

/**
 * Provides a controlled and consistent environment with which to test the plugin.
 */
class ControllerStub extends LaminasAbstractActionController
{
    public function __construct(protected Placeholder $placeholder)
    {
    }

    public function getPlaceholder(): Placeholder
    {
        return $this->placeholder;
    }

    /**
     * Method to test the invoking of the plugin with array of options
     * @param $options
     * @return mixed
     */
    public function pluginInvoke($options)
    {
        return $this->ElasticSearch($options);
    }

    /**
     * Method to return the plugin
     * @return mixed
     */
    public function getPlugin()
    {
        return $this->ElasticSearch();
    }

    /**
     * Method called by controller as a result of plugin calls. Not tested here.
     *
     * @param string|ViewModel $view
     * @param null $pageTitle
     * @param null $pageSubTitle
     * @return string|ViewModel
     */
    public function renderView($view, $pageTitle = null, $pageSubTitle = null)
    {
        $view->pageTitle = $pageTitle;
        $view->pageSubTitle = $pageSubTitle;

        return $view;
    }
}
