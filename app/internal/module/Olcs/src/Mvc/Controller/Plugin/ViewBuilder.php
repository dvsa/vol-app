<?php

namespace Olcs\Mvc\Controller\Plugin;

use Olcs\Controller\Interfaces\HeaderTemplateProvider;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\View\Builder\Builder;
use Olcs\View\Builder\PageLayoutBuilder;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Class ViewBuilder
 * @package Olcs\Mvc\Controller\Plugin
 */
class ViewBuilder extends AbstractPlugin
{
    /**
     * @var
     */
    private $viewBuilder;

    /**
     * @return null
     */
    public function __invoke()
    {
        if ($this->viewBuilder === null) {
            $this->setupViewBuilder();
        }

        return $this->viewBuilder;
    }

    /**
     * @return \Zend\Mvc\Controller\AbstractActionController
     */
    public function getController()
    {
        return parent::getController();
    }

    /**
     *
     */
    private function setupViewBuilder()
    {
        $controller = $this->getController();

        $baseTemplate = $controller->getRequest()->isXmlHttpRequest() ? 'ajax' : 'base';
        $headerTemplate = ($controller instanceof HeaderTemplateProvider) ?
            $controller->getHeaderTemplate() : 'partials/header';

        $viewBuilder = new Builder($headerTemplate, $baseTemplate);

        if ($controller instanceof PageLayoutProvider) {
            $viewBuilder = new PageLayoutBuilder($viewBuilder, $controller->getPageLayout());
        }

        if ($controller instanceof PageInnerLayoutProvider) {
            $viewBuilder = new PageLayoutBuilder($viewBuilder, $controller->getPageInnerLayout());
        }

        $this->viewBuilder = $viewBuilder;
    }
}
