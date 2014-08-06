<?php
/**
 * Publication Controller
 */

namespace Admin\Controller;

use Common\Controller\FormActionController;

/**
 * Publication Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class PublicationController extends FormActionController
{
    public function indexAction()
    {
        $view = $this->getView();
        $view->setTemplate('publication/index');
        return $view;
    }
}
