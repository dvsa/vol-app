<?php
/**
 * Public Holiday Controller
 */

namespace Admin\Controller;

use Common\Controller\FormActionController;

/**
 * Public Holiday Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class PublicHolidayController extends FormActionController
{
    public function indexAction()
    {
        $view = $this->getView();
        $view->setTemplate('admin/page/public-holiday');
        return $view;
    }
}
