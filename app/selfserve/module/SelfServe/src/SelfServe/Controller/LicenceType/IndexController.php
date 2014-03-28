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

use Common\Controller\FormActionController;
use Zend\View\Model\ViewModel;
use SelfServe\Form\LicenceType\LicenceTypeForm;

class IndexController extends FormActionController
{
    protected $messages;
    protected $section = 'licence_type';
    
    public function IndexAction() {
       
        $licenceTypeForm = new LicenceTypeForm();
        $step = $this->getEvent()->getRouteMatch()->getParam('step');

        $licenceTypeForm = $this->configureForm('licenceType', $licenceTypeForm, $step);
        
        $routeParams = $this->getEvent()->getRouteMatch()->getParams();
        $request  = $this->getRequest();
                     
        if ($request->isPost()) {

            $licenceTypeForm->setData($request->getPost());
            if ($licenceTypeForm->isValid()) {

                $this->persistFormData($licenceTypeForm, $step);
                
                $next_step = $this->getNextStepRoute('licenceType', $licenceTypeForm, $step);
                //var_dump($next_step);exit;
                if ($next_step == 'Complete')
                {
                    return $this->forward()->dispatch('SelfServe\LicenceType\Index', array('action' => 'complete'));
                                    
                } else {
                    $this->redirect()->toUrl($next_step);
                }
            } else {
                $this->addErrorMessage('An error occurred.', \Zend\Log\Logger::ERR);
                $this->messages = $licenceTypeForm->getMessages();
            }
        } 
             
        $persistedFormData = $this->getPersistedFormData($licenceTypeForm);
        
        if (!empty($persistedFormData))
        {
            $licenceTypeForm->setData($persistedFormData);
        }
        
        //$headers = $this->getResponse()->getHeaders();
        //$headers->addHeaderLine('Cache-Control: no-cache');             
        $view = new ViewModel(array('licenceTypeForm' => $licenceTypeForm,
                                    'messages' => $this->messages));

        $view->setTemplate('self-serve/index/index');
        return $view;
    }

    public function completeAction()
    {

        $request  = $this->getRequest();
       
        $licenceTypeForm = new LicenceTypeForm();
        $step = $this->getEvent()->getRouteMatch()->getParam('step');
        
        $session = new \Zend\Session\Container('LicenceTypeForm');

        $persistedFormData[] = $session->Location; 
        $persistedFormData[] = $session->OperatorType;
        $persistedFormData[] = $session->LicenceType;
       
        $view = new ViewModel(array('persistedFormData' => $persistedFormData,
                                    'messages' => $this->messages));

        $view->setTemplate('self-serve/index/complete');
        return $view;        
    }
    
    public function newIndexAction()
    {
        
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

}
