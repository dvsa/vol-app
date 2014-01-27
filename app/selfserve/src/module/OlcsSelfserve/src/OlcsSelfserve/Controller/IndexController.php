<?php
/**
 * OLCS Self-Service Index
 */

namespace OlcsSelfserve\Controller;

use OlcsCommon\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

class IndexController extends AbstractActionController {
    
    public function indexAction()    {
        
       $view = new ViewModel(array(
            'message' => 'OLCS Self-Service Dashboard',
        ));
        $view->setTemplate('index/index');
        return $view;        
    }
    
}
