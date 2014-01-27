<?php
/**
 * OLCS Self-Service
 * Licence Type page controller
 * @author      Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 */

namespace OlcsSelfserve\Controller;

use OlcsCommon\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use OlcsSelfserve\Form;

class LicenceTypeController extends AbstractActionController {
    
    public function indexAction()    {
        
        $licenceTypeForm = new Form\Application\LicenceTypeForm();
        $view = new ViewModel(array('licenceTypeForm' => $licenceTypeForm,
                                    'messages' => array()));
        $view->setTemplate('selfserve/application/licenceType');
        return $view;        
    }
    
}
