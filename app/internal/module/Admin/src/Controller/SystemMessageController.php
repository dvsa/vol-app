<?php
/**
 * System Message Controller
 */

namespace Admin\Controller;

use \Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;
use Common\Controller\Traits\GenericRenderView;

/**
 * System Message Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class SystemMessageController extends ZendAbstractActionController
{
    use GenericRenderView;

    public function indexAction()
    {
        $view = $this->getView();
        $view->setTemplate('placeholder');

        $this->placeholder()->setPlaceholder('pageTitle', 'System messages');

        return $this->viewBuilder()->buildView($view);
    }
}
