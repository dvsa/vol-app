<?php

namespace Admin\Controller\Traits;

use Laminas\View\Model\ViewModel;

/**
 * Shared left view function which enforces RBAC menu rendering based on user.
 */
trait ReportLeftViewTrait
{
    /**
     * Get left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $viewData = [
            'navigationId' => 'admin-dashboard/admin-report',
            'navigationTitle' => 'Reports',
            'removePageIds' => ['admin-dashboard/admin-report/permits']
        ];

        if ($this->currentUser()->getUserData()['dataAccess']['isIrfo']) {
            unset($viewData['removePageIds']);
        }

        $view = new ViewModel(
            $viewData
        );

        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }
}
