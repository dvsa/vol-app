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

    /**
     * Display messages and enable to user to carry on to a decision if applicable.
     *
     * @return string|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function activeLicenceCheckAction()
    {
        $decision = $this->fromRoute('decision', null);
        $licence = $this->fromRoute('licence', null);

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $licenceStatusHelper = $this->getServiceLocator()->get('Helper\LicenceStatus');

        $active = $licenceStatusHelper->isLicenceActive($licence);

        $messages = array_map(
            function ($message) use ($translator) {
                if (is_array($message)) {
                    return $translator->translate($message['message']);
                }
            },
            $licenceStatusHelper->getMessages()
        );

        $form = $formHelper->createFormWithRequest('LicenceStatusDecisionMessages', $this->getRequest());

        switch ($decision) {
            case 'suspend':
            case 'curtail':
            case 'surrender':
                if ($this->getRequest()->isPost() || !$active) {
                    return $this->redirectToDecision($decision, $licence);
                }
                break;
            case 'revoke':
                if (!$active) {
                    return $this->redirectToDecision($decision, $licence);
                }
                $form->get('form-actions')->remove('continue');
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

    /**
     * Curtail a licence.
     *
     * @return string|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function curtailAction()
    {
        $licenceId = $this->fromRoute('licence');

        if ($this->isButtonPressed('curtailNow')) {
            return $this->affectImmediate(
                $licenceId,
                'curtailNow',
                'licence-status.curtailment.message.save.success'
            );
        }

        $form = $this->getDecisionForm('LicenceStatusDecisionCurtail');

        if ($this->getRequest()->isPost()) {
            $form->setData((array)$this->getRequest()->getPost());

            if ($form->isValid()) {
                $formData = $form->getData();
                $this->saveDecisionForLicence(
                    $licenceId,
                    array(
                        'licenceStatus' => LicenceStatusRuleEntityService::LICENCE_STATUS_RULE_CURTAILED,
                        'startDate' => $formData['licence-decision']['curtailFrom'],
                        'endDate' => $formData['licence-decision']['curtailTo'],
                    )
                );

                $this->flashMessenger()->addSuccessMessage('licence-status.curtailment.message.save.success');

                return $this->redirectToRouteAjax('licence', array('licence' => $licenceId));
            }
        }

        return $this->renderDecisionView($form);
    }

    /**
     * Revoke a licence.
     *
     * @return string|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function revokeAction()
    {
        $licenceId = $this->fromRoute('licence');

        if ($this->isButtonPressed('revokeNow')) {
            return $this->affectImmediate(
                $licenceId,
                'revokeNow',
                'licence-status.revocation.message.save.success'
            );
        }

        $form = $this->getDecisionForm('LicenceStatusDecisionRevoke');

        if ($this->getRequest()->isPost()) {
            $form->setData((array)$this->getRequest()->getPost());

            if ($form->isValid()) {
                $formData = $form->getData();

                $this->saveDecisionForLicence(
                    $licenceId,
                    array(
                        'licenceStatus' => LicenceStatusRuleEntityService::LICENCE_STATUS_RULE_REVOKED,
                        'startDate' => $formData['licence-decision']['revokeFrom'],
                    )
                );

                $this->flashMessenger()->addSuccessMessage('licence-status.revocation.message.save.success');

                return $this->redirectToRouteAjax('licence', array('licence' => $licenceId));
            }
        }

        return $this->renderDecisionView($form);
    }

    /**
     * Suspend a licence.
     *
     * @return string|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function suspendAction()
    {
        $licenceId = $this->fromRoute('licence');

        if ($this->isButtonPressed('suspendNow')) {
            return $this->affectImmediate(
                $licenceId,
                'suspendNow',
                'licence-status.suspension.message.save.success'
            );
        }

        $form = $this->getDecisionForm('LicenceStatusDecisionSuspend');

        if ($this->getRequest()->isPost()) {
            $form->setData((array)$this->getRequest()->getPost());

            if ($form->isValid()) {
                $formData = $form->getData();
                $this->saveDecisionForLicence(
                    $licenceId,
                    array(
                        'licenceStatus' => LicenceStatusRuleEntityService::LICENCE_STATUS_RULE_SUSPENDED,
                        'startDate' => $formData['licence-decision']['suspendFrom'],
                        'endDate' => $formData['licence-decision']['suspendTo']
                    )
                );

                $this->flashMessenger()->addSuccessMessage('licence-status.suspension.message.save.success');

                return $this->redirectToRouteAjax('licence', array('licence' => $licenceId));
            }
        }

        return $this->renderDecisionView($form);
    }

    /**
     * Reset the licence back to a valid state.
     *
     * @return string|\Zend\View\Model\ViewModel
     */
    public function resetToValidAction()
    {
        $licenceId = $this->fromRoute('licence');

        $form = $this->getDecisionForm('GenericConfirmation');
        $form->get('messages')
            ->get('message')
            ->setValue('licence-status.reset.message');
        $form->get('form-actions')
            ->get('submit')
            ->setLabel('licence-status.reset.title');

        if ($this->getRequest()->isPost()) {
            $form->setData((array)$this->getRequest()->getPost());

            if ($form->isValid()) {
                $licenceStatusHelperService = $this->getServiceLocator()->get('Helper\LicenceStatus');
                $licenceStatusHelperService->removeStatusRulesByLicenceAndType(
                    $licenceId,
                    array()
                );
            }
        }

        return $this->renderView(
            $this->getView(
                array(
                    'form' => $form,
                )
            )->setTemplate('partials/form'),
            'licence-status.reset.title'
        );
    }

    /**
     * Surrender a licence.
     *
     * @return string|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function surrenderAction()
    {
        $licenceId = $this->fromRoute('licence');

        $form = $this->getDecisionForm('LicenceStatusDecisionSurrender');

        if ($this->getRequest()->isPost()) {

            $form->setData((array)$this->getRequest()->getPost());

            if ($form->isValid()) {
                $formData = $form->getData();

                $surrenderDate = $formData['licence-decision']['surrenderDate'];

                $this->getServiceLocator()->get('Helper\LicenceStatus')
                    ->surrenderNow($licenceId, $surrenderDate);

                $this->flashMessenger()->addSuccessMessage('licence-status.surrender.message.save.success');

                return $this->redirectToRouteAjax('licence', array('licence' => $licenceId));
            }
        }

        return $this->renderDecisionView($form);
    }

    /**
     * If a xNow e.g. curtailNow method has been pressed then redirect.
     *
     * @param null|int $licenceId The licence id.
     * @param null|string $function The function to call on the helper.
     * @param null|string $message The message to display
     *
     * @return \Zend\Http\Response A redirection response.
     */
    private function affectImmediate($licenceId = null, $function = null, $message = null)
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

    /**
     * Get the decision form.
     *
     * @param null|string $name The form name to try and get.
     *
     * @return mixed The form.
     */
    private function getDecisionForm($name = null)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        return $formHelper->createFormWithRequest($name, $this->getRequest());
    }

    /**
     * Save a decision against a licence.
     *
     * @param null|int $licenceId The licence id.
     * @param array $data The data to save.
     */
    private function saveDecisionForLicence($licenceId = null, array $data = array())
    {
        $licenceStatusEntityService = $this->getServiceLocator()->get('Entity\LicenceStatusRule');
        $licenceStatusEntityService->createStatusForLicence(
            $licenceId,
            array(
                'data' => $data
            )
        );
    }

    /**
     * Render the view with the form.
     *
     * @param null|\Common\Form\Form The form to render.
     * @param bool Whether tp load the script files.
     *
     * @return string|\Zend\View\Model\ViewModel
     */
    private function renderDecisionView($form = null)
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

    /**
     * Redirect the request to a specific decision.
     *
     * @param null|string $decision The decision.
     * @param null|int $licence The licence id.
     *
     * @return \Zend\Http\Response The redirection
     */
    private function redirectToDecision($decision = null, $licence = null)
    {
        return $this->redirectToRoute(
            'licence/' . $decision . '-licence',
            array(
                'licence' => $licence
            )
        );
    }
}
