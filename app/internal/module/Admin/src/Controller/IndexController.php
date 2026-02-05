<?php

namespace Admin\Controller;

use Laminas\View\Model\ViewModel;

class IndexController extends AbstractController
{
    #[\Override]
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
    #[\Override]
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('admin/sections/admin/partials/home-left');

        return $view;
    }
}
