<?php

/**
 * LicenceDecisionsController.php
 */
namespace Olcs\Controller\Licence;

use Common\Service\Entity\LicenceStatusRuleEntityService;
use Olcs\Controller\AbstractController;

use Olcs\Controller\Traits\LicenceControllerTrait;

/**
 * Class LicenceDecisionsController
 *
 * Calling code for logic around actions directly against the licence. E.g.
 * suspending or revoking the licence for a specified amount of time.
 *
 * @package Olcs\Controller\Licence
 */
class LicenceDecisionsController extends AbstractController
{
    use LicenceControllerTrait;

    public function activeLicenceCheckAction()
    {
        $decision = $this->fromRoute('decision', null);
        $licence = $this->fromRoute('licence', null);

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $licenceStatusHelper = $this->getServiceLocator()->get('Helper\LicenceStatus');

        $form = $formHelper->createFormWithRequest('LicenceStatusDecisionMessages', $this->getRequest());

        $messages = array_map(
            function ($message) use ($translator) {
                if (is_array($message)) {
                    return $translator->translate($message['message']);
                }
            },
            $licenceStatusHelper->isLicenceActive($licence)
        );

        switch ($decision) {
            case 'suspend':
            case 'curtail':
                if ($this->getRequest()->isPost() && empty($messages)) {
                    return $this->redirectToDecision($decision, $licence);
                }
                break;
            case 'revoke':
                if (empty($messages)) {
                    
                    return $this->redirectToDecision($decision, $licence);
                }
                break;
        }

        $form->get('messages')->get('message')->setValue(implode('<br>', $messages));

        $view = $this->getViewWithLicence(
            array(
                'form' => $form
            )
        );

        $view->setTemplate('partials/form');

        return $this->renderView($view);
    }

    public function curtailAction()
    {
        $licenceId = $this->fromRoute('licence');

        if ($this->isButtonPressed('curtailNow')) {
            return $this->affectImmediate(
                $licenceId,
                'curtailNow',
                'The curtailment details have been saved'
            );
        }

        $form = $this->getDecisionForm('LicenceStatusDecisionCurtail');

        if ($this->request->isPost()) {
            $form->setData((array)$this->request->getPost());

            if ($form->isValid()) {
                $formData = $form->getData();
                $this->saveDecisionForLicence($form, $licenceId, array(
                    'licenceStatus' => LicenceStatusRuleEntityService::LICENCE_STATUS_RULE_CURTAILED,
                    'startDate' => $formData['licence-decision']['curtailFrom'],
                    'endDate' => $formData['licence-decision']['curtailTo'],
                ));

                $this->flashMessenger()->addSuccessMessage('The curtailment details have been saved');

                return $this->redirectToRouteAjax('licence', array('licence' => $licenceId));
            }
        }

        return $this->renderDecisionView($form);
    }

    public function revokeAction()
    {
        $licenceId = $this->fromRoute('licence');

        if ($this->isButtonPressed('revokeNow')) {
            return $this->affectImmediate(
                $licenceId,
                'revokeNow',
                'The revocation details have been saved'
            );
        }

        $form = $this->getDecisionForm('LicenceStatusDecisionRevoke');

        if ($this->request->isPost()) {
            $form->setData((array)$this->request->getPost());

            if ($form->isValid()) {
                $formData = $form->getData();

                $this->saveDecisionForLicence($form, $licenceId, array(
                    'licenceStatus' => LicenceStatusRuleEntityService::LICENCE_STATUS_RULE_REVOKED,
                    'startDate' => $formData['licence-decision']['revokeFrom'],
                ));

                $this->flashMessenger()->addSuccessMessage('The revocation details have been saved');

                return $this->redirectToRouteAjax('licence', array('licence' => $licenceId));
            }
        }

        return $this->renderDecisionView($form);
    }

    public function suspendAction()
    {
        $licenceId = $this->fromRoute('licence');

        if ($this->isButtonPressed('suspendNow')) {
            return $this->affectImmediate(
                $licenceId,
                'suspendNow',
                'The suspension details have been saved'
            );
        }

        $form = $this->getDecisionForm('LicenceStatusDecisionSuspend');

        if ($this->request->isPost()) {
            $form->setData((array)$this->request->getPost());

            if ($form->isValid()) {
                $formData = $form->getData();
                $this->saveDecisionForLicence($form, $licenceId, array(
                    'licenceStatus' => LicenceStatusRuleEntityService::LICENCE_STATUS_RULE_SUSPENDED,
                    'startDate' => $formData['licence-decision']['suspendFrom'],
                    'endDate' => $formData['licence-decision']['suspendTo'],
                ));

                $this->flashMessenger()->addSuccessMessage('The suspension details have been saved');

                return $this->redirectToRouteAjax('licence', array('licence' => $licenceId));
            }
        }

        return $this->renderDecisionView($form);
    }



    private function affectImmediate($licenceId, $function, $message)
    {
        $licenceStatusHelper = $this->getServiceLocator()->get('Helper\LicenceStatus');

        $licenceStatusHelper->$function($licenceId);
        $this->flashMessenger()->addSuccessMessage($message);
        return $this->redirectToRouteAjax(
            'licence',
            array(
                'licence' => $licenceId
            )
        );
    }

    private function getDecisionForm($name)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        return $formHelper->createFormWithRequest($name, $this->getRequest());
    }

    private function saveDecisionForLicence($form, $licenceId, $data)
    {
        $licenceStatusEntityService = $this->getServiceLocator()->get('Entity\LicenceStatusRule');
        $licenceStatusEntityService->createStatusForLicence(
            $licenceId,
            array(
                $data
            )
        );
    }

    private function renderDecisionView($form)
    {
        $view = $this->getViewWithLicence(
            array(
                'form' => $form
            )
        );

        $this->getServiceLocator()->get('Script')->loadFiles(['forms/licence-decision']);

        $view->setTemplate('partials/form');

        return $this->renderView($view);
    }

    private function redirectToDecision($decision, $licence)
    {
        if (method_exists(__CLASS__, $decision . 'Action')) {
            $this->redirectToRoute('licence/' . $decision . '-licence', array(
                'licence' => $licence
            ));
        }
    }
}
