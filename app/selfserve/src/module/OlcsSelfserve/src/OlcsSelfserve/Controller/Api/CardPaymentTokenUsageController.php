<?php
/**
 * CardPaymentTokenUsage Service API Controller
 *
 * @package     olcs
 * @subpackage  service-api
 * @author      Mike Cooper
 */

namespace Olcs\Controller\Api;

use OlcsCommon\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class CardPaymentTokenUsageController extends AbstractRestfulController
{
    public function getList()
    {
        $cardPaymentTokenUsageService = $this->getServiceLocator()->get('CardPaymentTokenUsageServiceFactory');

        $data = array(
            'rows' => $cardPaymentTokenUsageService->getList()
        );

        return new JsonModel($data);
    }
    
    public function get($token)
    {
        $cardPaymentTokenUsageService = $this->getServiceLocator()->get('CardPaymentTokenUsageServiceFactory');
        $data = $cardPaymentTokenUsageService->get($token);
        if ($data['token'] != $token) {
            //$this->getResponse()->setStatusCode(400);
            return new JsonModel(array('error' => AbstractRestfulController::ERROR_NOT_FOUND));
        }
        return new JsonModel($data);
    }
    
    public function create($params)
    {
        if ($this->getRequest()->isPost()) {
            $cardPaymentTokenUsageService = $this->getServiceLocator()->get('CardPaymentTokenUsageServiceFactory');
            $paymentParams = array('token' => $params['token'], 
                                            'status' => 'requested');
            $data = $cardPaymentTokenUsageService->create($paymentParams);
            if ($data != $params['token']) {
                //$this->getResponse()->setStatusCode(400);
                return new JsonModel(array('error' => AbstractRestfulController::ERROR_UNKNOWN));
            }
        } else {
            //$this->getResponse()->setStatusCode(405);
            return new JsonModel(array('error' => self::ERROR_METHOD_NOT_ALLOWED));
        }
        return new JsonModel(array('success'));
    }
}
