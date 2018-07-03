<?php

namespace Admin\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;

/**
 * Permits Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PermitsController extends AbstractInternalController implements LeftViewProvider, ToggleAwareInterface
{
    protected $navigationId = 'admin-dashboard/admin-permits';

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

    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Permits');
        $view = new ViewModel();
        $view->setTemplate('pages/placeholder.phtml');
        return $view;
    }
}
