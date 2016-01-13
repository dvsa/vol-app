<?php

/**
 * Public Holiday Controller
 */
namespace Admin\Controller;

use Olcs\Controller\AbstractInternalController;
use Common\Controller\Traits\GenericRenderView;

/**
 * Public Holiday Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PublicHolidayController extends AbstractInternalController
{
    use GenericRenderView;

    public function indexAction()
    {
        $view = $this->getView();
        $view->setTemplate('placeholder');

        $this->placeholder()->setPlaceholder('pageTitle', 'Public holiday');

        return $this->viewBuilder()->buildView($view);
    }
}
