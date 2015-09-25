<?php

/**
 * Public Holiday Controller
 */
namespace Admin\Controller;

use Common\Controller\AbstractActionController;

/**
 * Public Holiday Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PublicHolidayController extends AbstractActionController
{
    public function indexAction()
    {
        $view = $this->getView();
        $view->setTemplate('placeholder');

        $this->placeholder()->setPlaceholder('pageTitle', 'Public holiday');

        return $this->viewBuilder()->buildView($view);
    }
}
