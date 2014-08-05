<?php
/**
 * Public Holiday Controller
 */

namespace Admin\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Public Holiday Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class PublicHolidayController extends AbstractActionController
{
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('admin/page/public-holiday.phtml');
        return $view;
    }
}
