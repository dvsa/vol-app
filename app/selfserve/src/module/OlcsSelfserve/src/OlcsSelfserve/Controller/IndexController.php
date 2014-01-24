<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace OlcsSelfserve\Controller;

use OlcsCommon\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

class IndexController extends AbstractActionController {
    
    public function indexAction()    {
        
       $view = new ViewModel(array(
            'message' => 'OCLS dashboard',
        ));
        $view->setTemplate('index/index');
        return $view;        
    }
    
}
