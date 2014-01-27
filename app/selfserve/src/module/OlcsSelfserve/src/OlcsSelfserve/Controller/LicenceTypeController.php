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

class LicenceTypeController extends AbstractActionController {
    
    public function indexAction()    {
        
       $view = new ViewModel(array(
            'message' => 'Type of Licence',
        ));
        $view->setTemplate('selfserve/application/licenceType');
        return $view;        
    }
    
}
