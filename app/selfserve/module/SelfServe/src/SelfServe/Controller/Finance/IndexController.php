<?php

/**
 * AuthorisationFinance Controller
 *
 *
 * @package		selfserve
 * @subpackage          operating-centre
 * @author		S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace SelfServe\Controller\Finance;

use Common\Controller\FormJourneyActionController;
use Zend\View\Model\ViewModel;

class IndexController extends FormJourneyActionController
{
    protected $messages;
    protected $section = 'finance';
    
    public function indexAction() {
               
        // render the view
        $view = new ViewModel();
        $view->setTemplate('self-serve/finance/index');
        return $view;

    }

    /**
     * End of the journey redirect to business type
     */
    public function completeAction()
    {

        // persist data if possible
        $request  = $this->getRequest();
       
        $this->redirect()->toRoute('selfserve/business', ['step' => 'business_type']);

    }


}
