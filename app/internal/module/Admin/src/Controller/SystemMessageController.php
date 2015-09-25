<?php
/**
 * System Message Controller
 */

namespace Admin\Controller;

use Common\Controller\AbstractActionController;

/**
 * System Message Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class SystemMessageController extends AbstractActionController
{
    public function indexAction()
    {
        $view = $this->getView();
        $view->setTemplate('placeholder');

        $this->placeholder()->setPlaceholder('pageTitle', 'System messages');

        return $this->viewBuilder()->buildView($view);
    }
}
