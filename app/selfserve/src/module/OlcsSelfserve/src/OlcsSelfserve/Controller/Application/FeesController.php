<?php

/**
 * Dsiplay fees(invoices) for a licence application and make payments.
 *
 * OLCS-437
 *
 * @package		olcs
 * @subpackage	application
 * @author		Mike Cooper 
 */

namespace Olcs\Controller\Application;

use OlcsCommon\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Olcs\Form;
use Zend\View\Model\JsonModel;

class FeesController extends AbstractActionController
{
    /*
     * Displays the fees page, recieves a post from the select box and calls the vosa payment service to create the transaction and 
     * the Olcs payment service to create a payment entry.
     */
    public function feesListAction() 
    {
        
        $appId = $this->getEvent()->getRouteMatch()->getParam('appId');
        if ($this->getRequest()->isPost()) {
            // To handle payment type requests other than card and redirect back to feesList
            return $this->redirect()->toRoute('application_fees_list', array('appId'=>$appId));
        }
        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('label', 'create new application');
        $view = new ViewModel();
        $data = $this->service('Olcs\Application')->get($appId.'/fees');
        $view->setVariable('dataList', ($data));
        
        // If the final redirect is back to here the status is set to tell the view which overlay to show.
        $status = $this->getRequest()->getQuery('success') ? 'success' : ($this->getRequest()->getQuery('declined') ? 'declined' : false);
        $view->setVariable('ajaxUrl', '/application/'.$appId.'/ajax-get-payment');
        if ($status) {
            $view->setVariable('status', $status);
            $view->setVariable('invoices', $this->getRequest()->getQuery('v'));
        }
        $view->setTemplate('olcs/application/fees/feesList');
        return $view;
    }
    
    public function ajaxGetPaymentAction() 
    {
        $params = $this->getRequest()->getPost();
        switch ($params['paymentTypeSelect']) {
            case 'card-payment':
                    return $this->getCardPaymentForm();
                break;
            case 'receipt':
            case 'account-balance':
                    return $this->getPayForm($params['paymentTypeSelect']);
                break;
        }
        return new JsonModel($params);
    }
    
    public function getPayForm($paymentType) 
    {
        $payForm = new Form\Application\Fees\FeesPayForm('payForm');
        //$payForm->get('formPaymentType')->setValue($paymentType);
        $paymentIFrameView = new ViewModel(array('payForm'=>$payForm));
        $paymentIFrameView->setVariable('payType', $paymentType);
        $paymentIFrameView->setTemplate('olcs/application/fees/payForm');
        $paymentIFrameView->setTerminal(true);
        return $paymentIFrameView;
    }
    
    public function getCardPaymentForm() 
    {
        $invoices = $this->getRequest()->getPost('invoices');
        $tokenData = $this->initiateCardPaymentForm($invoices);
        if (isset($tokenData['access_token']) 
            && isset($tokenData['gateway_url'])
            && isset($tokenData['expires_in'])
            && isset($tokenData['token_type'])
            &&$tokenData['token_type'] == 'Bearer') {
                $params = array('invoices' => $invoices,
                                            'token'=> $tokenData['access_token'],
                                            'status' => 'in progress',
                                            'payment_method'=>'card');
                $this->initiateOlcsPayment($params);
                // Adds view params for the iframe source and the redirect URL
                $paymentIFrameView = new ViewModel(array('iframeUrl' => $tokenData['gateway_url']));
                $paymentIFrameView->setTemplate('olcs/application/fees/paymentIFrame');
                $paymentIFrameView->setTerminal(true);
                return $paymentIFrameView;
        } else {
            throw new \Exception("Service '/api/olcs-payment' did not return as success");
        }
    }
    
    private function initiateCardPaymentForm($invoices) 
    {
        $appId = $this->getEvent()->getRouteMatch()->getParam('appId');
        $data = $this->service('Olcs\Payment')->post('fees', $invoices);
        $invoice = 123;
        $invoiceBase64 = base64_encode(implode(',', $invoices));
        $config = $this->getServiceLocator()->get('config')['olcs_client_details'];
        $params = array('grant_type' => "client_credentials",
                                    'client_id' => $config['client_id'],
                                    'client_secret' => $config['client_secret'],
                                    'scope' => 'payment',
                                    'redirect_uri' => '/application/'.$appId.'/'.rawurlencode($invoiceBase64).'/complete',
                                    'payment_data' => array('amount' => (float)$data['totalFees']));
        // Calls the payment service to initiate the payment and get the token and Gateway URL
        $tokenData = $this->service('Vosa\Service\Payment')->post('/token', $params);
        return $tokenData;
    }
    
    private function initiateOlcsPayment($params) 
    {
        try {
                // Calls the OLCS API payment controller to create the payment in the OLCS database
                $data = $this->service('Olcs\Payment')->post('create', $params);
                if (!isset($data['success'])) {
                    throw new \Exception("Service '/api/olcs-payment' did not return as success");
                    exit;
                }
            } catch (Exception $e) {
                throw $e;
                exit;
            }
        return true;
    }
     
    /*
     * The complete action gets the redirect setup in the payment stub and it's associated query string variables indicating the status
     * from the gateway provider. This may have to change once a real card provider is adopted.
     */
    public function completeAction()
    {
        $view = new ViewModel();
        $invoices = explode(',', base64_decode($this->getEvent()->getRouteMatch()->getParam('invoice')));
        //TODO: Call the card payment complete method
        $config = $this->getServiceLocator()->get('config')['olcs_client_details'];
        $params = array('client_id' => $config['client_id'],
                                        'client_secret' => $config['client_secret'],
                                        'access_token' => $this->getRequest()->getQuery('token'));
        try{
            $tokenData = $this->service('Vosa\Service\Payment')->post('/complete', $params);
            if (!isset($tokenData['payment_success'])) {
                throw new \Exception("Service '/api/payment/complete' did not return as success");
            }
        } catch (Exception $e) {
            throw $e;
            exit;
        }
        $appId = $this->getEvent()->getRouteMatch()->getParam('appId');
        $invoices = $this->getEvent()->getRouteMatch()->getParam('invoices');
        if ($this->getRequest()->getQuery('error')) {
            $status = 'declined';
        } else/*if($this->getRequest()->getQuery('success'))*/ {
            $status = 'success';
        }
        $redirect_url = '/application/'.$appId.'/fees';
        if (isset($status)) {
            $olcsPaymentParams = array('access_token'=>$this->getRequest()->getQuery('token'),
                                                                'status'=>$status);
            try {
                $data = $this->service('Olcs\Payment')->put('complete', $olcsPaymentParams);
                if (!isset($data['success'])) {
                    throw new \Exception("Service '/api/olcs-payment/complete' did not return as success");
                }
            } catch (Exception $e) {
                throw $e;
                exit;
            }
            $view->setVariables(array('redirectUrl' => $redirect_url.'?'.$status.'=1&v='.$invoices));
        }
        $view->setTerminal(true);
        $view->setTemplate('olcs/application/fees/complete');
        return $view;
    }
 
}
