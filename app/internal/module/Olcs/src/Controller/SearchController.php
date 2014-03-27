<?php

/**
 * @package    olcs
 * @subpackage
 * @author     Mike Cooper
 */

namespace Olcs\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Common\Exception\ResourceNotFoundException;
use Common\Exception\BadRequestException;
use OlcsEntities\Entity\User;
use Common\Exception\ResourceConflictException;

class SearchController extends AbstractActionController
{
    
    public function indexAction()
    {
        $form = $this->getForm('search');
        $callback = function() {
            $this->redirect()->toRoute('styleguide', array('action' => 'typography'));
        };
        $form = $this->formPost($form, $callback);
        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('form');
        return $view;
    }
    
    protected function redirectUser() {
        print 'Doing something like a redirect';
        $this->redirect()->toRoute('olcsHome');
    }
    
}
