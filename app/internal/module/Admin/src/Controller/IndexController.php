<?php

/**
 * IndexController
 */
namespace Admin\Controller;

use Zend\View\Model\ViewModel;

/**
 * IndexController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
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
