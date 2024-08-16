<?php

namespace Admin\Controller;

use Laminas\View\Model\ViewModel;

class IndexController extends AbstractController
{
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('admin-index');
        $this->setNavigationId('admin-dashboard');

        return $this->renderView($view, 'Admin');
    }

    /**
     * Get left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('admin/sections/admin/partials/home-left');

        return $view;
    }
}
