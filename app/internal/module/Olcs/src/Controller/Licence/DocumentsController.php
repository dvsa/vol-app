<?php
/**
 * Documents Controller
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
 */
namespace Olcs\Controller\Licence;

use Olcs\Controller\AbstractController;
use Zend\View\Model\ViewModel;

/**
 * Documents Controller
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
 */
class DocumentsController extends AbstractController
{
    protected $section = 'documents';

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('licence/documents/placeholder');

        return $this->renderView($view);
    }
}
