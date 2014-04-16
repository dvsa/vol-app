<?php

/**
 * @package    olcs
 * @subpackage
 * @author     Mike Cooper
 */

namespace Olcs\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('index/home.phtml');
        return $view;
    }

}
