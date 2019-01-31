<?php

namespace Admin\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;
use Common\Controller\Traits\GenericMethods;

/**
 * Permits Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PermitsController extends AbstractInternalController implements ToggleAwareInterface
{
    use GenericMethods;

    protected $navigationId = 'admin-dashboard/admin-permits';
    protected $tableViewTemplate = 'pages/irhp-permit-stock/index';

    protected $toggleConfig = [
        'default' => [
            FeatureToggle::ADMIN_PERMITS
        ],
    ];

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
