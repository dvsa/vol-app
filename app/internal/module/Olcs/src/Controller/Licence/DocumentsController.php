<?php
/**
 * Documents Controller
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
 */
namespace Olcs\Controller\Licence\Details;

use Zend\View\Model\ViewModel;

/**
 * Documents Controller
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
 */
class DocumentsController extends AbstractLicenceDetailsController
{
    protected $section = 'documents';

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('licence/documents/placeholder');

        return $this->renderView($view);
    }
}
