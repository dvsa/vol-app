<?php

/**
 * Index Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

use Olcs\View\Model\Dashboard;
use Common\Controller\Lva\AbstractController;
use Dvsa\Olcs\Transfer\Query\Organisation\Dashboard as DashboardQry;

/**
 * Index Controller
 */
class IndexController extends AbstractController
{
    /**
     * Dashboard index action
     */
    public function indexAction()
    {
        $view = new \Zend\View\Model\ViewModel();
        $view->setTemplate('index');
        return $view;
    }
}
