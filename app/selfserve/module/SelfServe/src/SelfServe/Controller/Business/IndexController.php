<?php

/**
 * Business Type Controller
 *
 *
 * @package		selfserve
 * @subpackage          business
 * @author		S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace SelfServe\Controller\Business;

use Common\Controller\FormJourneyActionController;
use Zend\View\Model\ViewModel;

class IndexController extends FormJourneyActionController
{
    protected $messages;
    protected $section = 'business';
    
    public function indexAction() {
       
        // create form
        $form = $this->generateSectionForm();
        
        // Do the post
        $form = $this->formPost($form, 'processForm');

        // prefill form data if persisted
        $formData = $this->getPersistedFormData($form);
        if (isset($formData))
        {
            $form->setData($formData);
        }
        
        // render the view
        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('self-serve/business/index');
        return $view;
    }

    /**
     * End of the journey redirect to business type
     */
    public function completeAction()
    {

        // persist data if possible
        $request  = $this->getRequest();
       
        $this->redirect()->toRoute('selfserve/business', ['step' => 'type']);

    }


}
