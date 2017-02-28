<?php

namespace Olcs\Controller;

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
        $applicationId = $this->params()->fromRoute('application');
        if ($applicationId) {
            // Save the application identifier so that when we come back from verify we know where to go
            $session = new \Olcs\Session\DigitalSignature();
            $session->setApplicationId($applicationId);
        } else {
            throw new \RuntimeException(
                'An entity identifier needs to be present, this is used to to calculate where'
                .' to return to after completing Verify'
            );
        }

        $form = $this->getServiceLocator()->get('Helper\Form')->createForm('VerifyRequest');

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
        $session->getManager()->getStorage()->clear(\Olcs\Session\DigitalSignature::SESSION_NAME);

        $dto = \Dvsa\Olcs\Transfer\Command\GdsVerify\ProcessSignatureResponse::create(
            ['samlResponse' => $this->getRequest()->getPost('SAMLResponse')]
        );
        if ($applicationId) {
            $dto->setApplication($applicationId);
        }
        $response = $this->handleCommand($dto);

        if ($applicationId) {
            if (!$response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('undertakings_not_signed');
            }

            return $this->redirect()->toRoute(
                'lva-application/undertakings',
                ['application' => $applicationId]
            );
        }

        throw new \RuntimeException('There was an error processing the signature response');
    }
}
