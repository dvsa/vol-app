<?php
/**
 * Payment Service API Controller
 *
 * @package     olcs
 * @subpackage  service-api
 * @author      Mike Cooper
 */

namespace Olcs\Controller\Api;

use OlcsCommon\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class PaymentController extends AbstractRestfulController
{
    public function getList()
    {
        $paymentService = $this->getServiceLocator()->get('PaymentServiceFactory'); 

        $data = array(
            'rows' => $paymentService->getPaymentData()
        );

        return new JsonModel($data);
    }
    
    public function get($id)
    {
        $paymentService = $this->getServiceLocator()->get('PaymentServiceFactory'); 
        $data = $paymentService->get($id);
        if (!$data) {
            return new JsonModel(array('error' => AbstractRestfulController::ERROR_NOT_FOUND));
        }
        return new JsonModel($data);
    }
    
    public function getFeesForPaymentAction()
    {
        $invoices = $this->processBodyContent($this->getRequest());
        $feeService = $this->getServiceLocator()->get('FeeServiceFactory');
        $totalFees = $feeService->SumSelectedFees($invoices);
        
        return new JsonModel($totalFees);
    }
    
    public function createAction()
    {
        if ($this->getRequest()->isPost()) {
            $paymentService = $this->getServiceLocator()->get('PaymentServiceFactory'); 
            $params = $this->processBodyContent($this->getRequest());
            $data = $paymentService->create($params);
            if (!isset($data['success'])) {
                return new JsonModel(array('error' => AbstractRestfulController::ERROR_UNKNOWN));
            }
        } else {
            return new JsonModel(array('error' => self::ERROR_METHOD_NOT_ALLOWED));
        }
        return new JsonModel($data);
    }
    
    public function completeAction()
    {
        if ($this->getRequest()->isPut()) {
            $params = $this->processBodyContent($this->getRequest());
            $paymentService = $this->getServiceLocator()->get('PaymentServiceFactory');
            $data = $paymentService->complete($params);
            if (!isset($data['success'])) {
                return new JsonModel(array('error' => AbstractRestfulController::ERROR_UNKNOWN));
            }
        } else {
            return new JsonModel(array('error' => self::ERROR_METHOD_NOT_ALLOWED));
        }
        return new JsonModel($data);
    }
}
