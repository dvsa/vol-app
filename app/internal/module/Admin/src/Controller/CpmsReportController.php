<?php

/**
 * Cpms Report Controller
 */
namespace Admin\Controller;

use Zend\View\Model\ViewModel;

/**
 * Cpms Report Controller
 */
class CpmsReportController extends AbstractController
{
    const DAILY_BALANCE_REPORT_ID = 'ED7AAFBC';

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('placeholder');

        $this->setNavigationId('admin-dashboard/admin-report');

        return $this->renderView($view, 'CPMS Financial report');
    }
}
