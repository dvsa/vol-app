<?php

/**
 * @package    olcs
 * @subpackage
 * @author     Mike Cooper
 */

namespace Olcs\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SearchController extends AbstractActionController
{

    public function indexAction()
    {
        $form = $this->generateFormWithData(
            'search',
            function() {
                $this->redirect()->toRoute('styleguide', array('action' => 'typography'));
            }
        );

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('form');
        return $view;
    }

    protected function redirectUser()
    {
        print 'Doing something like a redirect';
        $this->redirect()->toRoute('olcsHome');
    }

}
