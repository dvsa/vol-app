<?php
/**
 * Publication Controller
 */

namespace Admin\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Publication Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class PublicationController extends AbstractActionController
{
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('admin/page/publication.phtml');
        return $view;
    }
}
