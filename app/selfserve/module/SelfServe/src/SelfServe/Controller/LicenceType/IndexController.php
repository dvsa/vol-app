<?php

/**
 * AuthorisationFinance Controller
 *
 *
 * @package		selfserve
 * @subpackage          operating-centre
 * @author		S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace SelfServe\Controller\LicenceType;

use Common\Controller\FormJourneyActionController;
use Zend\View\Model\ViewModel;
use SelfServe\Form\LicenceType\LicenceTypeForm;

class IndexController extends FormJourneyActionController
{
    protected $messages;
    protected $section = 'licence_type';
    
    public function IndexAction() {
       
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
        $view = new ViewModel(['licenceTypeForm' => $form]);
        $view->setTemplate('self-serve/index/index');
        return $view;
    }

    /**
     * End of the journey redirect to business type
     */
    public function completeAction()
    {

        // persist data if possible
        $request  = $this->getRequest();
       
        $this->redirect()->toRoute('selfserve/business-type', ['step' => 'choose-company']);

    }


}
