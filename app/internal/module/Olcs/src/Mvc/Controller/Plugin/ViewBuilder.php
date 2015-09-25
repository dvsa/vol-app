<?php

namespace Olcs\Mvc\Controller\Plugin;

use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\NavigationIdProvider;
use Olcs\Controller\Interfaces\RightViewProvider;
use Olcs\View\Builder\Builder;
use Olcs\View\Model\ViewModel;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Class ViewBuilder
 * @package Olcs\Mvc\Controller\Plugin
 */
class ViewBuilder extends AbstractPlugin
{
    private $viewBuilder;

    public function __invoke()
    {
        if ($this->viewBuilder === null) {
            $this->setupViewBuilder();
        }

        return $this->viewBuilder;
    }

    private function setupViewBuilder()
    {
        $controller = $this->getController();

        $layout = new ViewModel();
        $layout->setIsAjax($controller->getRequest()->isXmlHttpRequest());

        if ($controller instanceof LeftViewProvider) {

            $left = $controller->getLeftView();

            if ($left !== null) {
                $layout->setLeft($left);
            }
        }

        if ($controller instanceof RightViewProvider) {

            $right = $controller->getRightView();

            if ($right !== null) {
                $layout->setRight($right);
            }
        }

        if ($controller instanceof NavigationIdProvider) {

            $navigationId = $controller->getNavigationId();

            if ($navigationId !== null) {
                $layout->setHorizontalNavigationId($navigationId);
            }
        }

        $this->viewBuilder = new Builder($layout);
    }
}
