<?php

/**
 * Abstract Action Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Common\Service\Table\TableBuilder;
use Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;

/**
 * Abstract Action Controller
 *
 * @NOTE Please don't make this a dumping ground
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractActionController extends ZendAbstractActionController
{
    /**
     * Render a table within the admin area
     *
     * @param TableBuilder $table
     * @param string $title
     * @param string $subTitle
     * @return ViewModel
     */
    protected function renderTable(TableBuilder $table, $title = null, $subTitle = null)
    {
        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('partials/table');

        return $this->renderView($view, $title, $subTitle);
    }

    /**
     * Wrapper method to render a view with optional title and sub title values
     *
     * @param ViewModel $view
     * @param string $pageTitle
     * @param string $pageSubTitle
     *
     * @return ViewModel
     */
    protected function renderView(ViewModel $view, $pageTitle = null, $pageSubTitle = null)
    {
        if ($view->terminate()) {
            return $view;
        }

        $viewVariables = array_merge(
            (array)$view->getVariables(),
            [
                'pageTitle' => $pageTitle,
                'pageSubTitle' => $pageSubTitle
            ]
        );

        // every page has a header, so no conditional logic needed here
        $header = new ViewModel($viewVariables);
        $header->setTemplate('partials/header');

        // we always inherit from the same base layout, unless the request
        // was asynchronous in which case we render a much simpler wrapper,
        // but one which will include any inline JS we need
        // note that if templates don't want this behaviour they can either
        // mark themselves as terminal, or simply not opt-in to this helper
        $template = $this->getRequest()->isXmlHttpRequest() ? 'ajax' : 'base';
        $base = new ViewModel();
        $base->setTemplate('layout/' . $template)
            ->setTerminal(true)
            ->setVariables($viewVariables)
            ->addChild($header, 'header')
            ->addChild($view, 'content');

        return $base;
    }
}
