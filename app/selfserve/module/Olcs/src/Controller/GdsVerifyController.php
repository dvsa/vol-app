<?php

namespace Olcs\Controller;


use Olcs\Logging\Log\Logger;
use Olcs\View\Model\Dashboard;
use Common\Controller\Lva\AbstractController;

/**
 * GdsVerifyController Controller
 */
class GdsVerifyController extends AbstractController
{
    /**
     * Display Form to initaite the GDS Verify identification process
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function initiateRequestAction()
    {
        $types = $this->getTypeOfRequest($this->params()->fromRoute());
        $form = $this->getServiceLocator()->get('Helper\Form')->createForm('VerifyRequest');
        $this->handleType($types);


        $response = $this->handleQuery(\Dvsa\Olcs\Transfer\Query\GdsVerify\GetAuthRequest::create([]));
        if ($response->isOk()) {
            $result = $response->getResult();
            if ($result['enabled'] !== true) {
                throw new \RuntimeException('Verify is currently disabled');
            }
            $form->setAttribute('action', $result['url']);
            $form->get('SAMLRequest')->setValue($result['samlRequest']);
        }

        $this->getServiceLocator()->get('Script')->loadFile('verify-request');

        return new \Zend\View\Model\ViewModel(array('form' => $form));
    }

    /**
     * Process the GDS Verify SAML response
     *
     * @return \Zend\Http\Response
     */
    public function processResponseAction()
    {
        $session = new \Olcs\Session\DigitalSignature();
        $applicationId = $session->hasApplicationId() ? $session->getApplicationId() : false;
        $continuationDetailId = $session->hasContinuationDetailId() ? $session->getContinuationDetailId() : false;
        $transportManagerApplicationId = $session->hasTransportManagerApplicationId() ? $session->getTransportManagerApplicationId() : false;
        $lva = $session->hasLva()? $session->getLva():'application';
        $role = $session->hasRole()?$session->getRole():null;

        $dto = \Dvsa\Olcs\Transfer\Command\GdsVerify\ProcessSignatureResponse::create(
            ['samlResponse' => $this->getRequest()->getPost('SAMLResponse')]
        );

        if ($applicationId) {
            $dto->setApplication($applicationId);
        }
        if ($continuationDetailId) {
            $dto->setContinuationDetail($continuationDetailId);
        }

        if ($transportManagerApplicationId) {
            $dto->setTransportManagerApplication($transportManagerApplicationId);
            $dto->setRole($role);
        }
        $session->getManager()->getStorage()->clear(\Olcs\Session\DigitalSignature::SESSION_NAME);
        $response = $this->handleCommand($dto);
        if (!$response->isOk()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('undertakings_not_signed');
        }


        if ($applicationId && !$transportManagerApplicationId) {
            return $this->redirect()->toRoute(
                'lva-application/undertakings',
                ['application' => $applicationId]
            );
        }

        if ($continuationDetailId) {
            return $this->redirect()->toRoute(
                'continuation/declaration',
                ['continuationDetailId' => $continuationDetailId]
            );
        }

        /** @var  $transportManagerApplicationId */
        if ($transportManagerApplicationId) {
            return $this->redirect()->toRoute(
                'lva-' . $lva . '/transport_manager_confirmation',
                [
                    'child_id' => $transportManagerApplicationId,
                    'application' => $applicationId,
                    'action' => 'index'
                ]
            );
        }



        throw new \RuntimeException('There was an error processing the signature response');
    }

    /**
     * verificationType
     *
     * @param array $types
     */
    private function handleType(array $types): void
    {
        $session = new \Olcs\Session\DigitalSignature();

        if (empty($types)) {
            throw new \RuntimeException(
                'An entity identifier needs to be present, this is used to to calculate where'
                . ' to return to after completing Verify'
            );
        }
        
        foreach ($types as $key => $value) {
            $methodName = 'set' . ucfirst($key);
            if (method_exists($session, $methodName)) {
                call_user_func([$session, $methodName], $value);
            }
        }
    }

    private function getTypeOfRequest($params): array
    {
        // remove controller and action keys from params
        $types = array_diff_assoc($params, ['controller' => self::class, 'action' => 'initiate-request']);
        return $types;
    }
}
