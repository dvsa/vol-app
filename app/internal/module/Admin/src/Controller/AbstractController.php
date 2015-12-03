<?php

/**
 * Abstract Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Admin\Controller;

use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;
use Common\Controller\Traits\GenericRenderView;
use Zend\View\Model\ViewModel;

/**
 * Abstract Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractController extends ZendAbstractActionController implements LeftViewProvider
{
    use GenericRenderView;

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('admin/sections/admin/partials/left');

        return $view;
    }

    protected function setNavigationId($id)
    {
        $this->getServiceLocator()->get('viewHelperManager')->get('placeholder')
            ->getContainer('navigationId')->set($id);
    }
}
