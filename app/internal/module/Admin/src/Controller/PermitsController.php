<?php

namespace Admin\Controller;

use Common\Controller\Traits\GenericMethods;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;

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
     * @return \Laminas\Http\Response
     */
    #[\Override]
    public function indexAction()
    {
        return $this->redirectToRoute('admin-dashboard/admin-permits/stocks', [], null, true);
    }
}
