<?php
/**
 * System Message Controller
 */

namespace Admin\Controller;

use Common\Controller\Traits\GenericRenderView;
use Olcs\Controller\AbstractInternalController;

/**
 * System Message Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class SystemMessageController extends AbstractInternalController
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
