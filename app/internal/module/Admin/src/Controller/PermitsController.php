<?php

namespace Admin\Controller;

use Olcs\Controller\AbstractInternalController;
use Zend\View\Model\ViewModel;
use Common\Controller\Traits\GenericMethods;

/**
 * Permits Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PermitsController extends AbstractInternalController
{
    use GenericMethods;

    protected $navigationId = 'admin-dashboard/admin-permits';
    protected $tableViewTemplate = 'pages/irhp-permit-stock/index';

    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-permits',
                'navigationTitle' => 'Permits'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * index action
     *
     * @return \Zend\Http\Response
     */
    public function indexAction()
    {
        return $this->redirectToRoute('admin-dashboard/admin-permits/stocks', [], null, true);
    }
}
