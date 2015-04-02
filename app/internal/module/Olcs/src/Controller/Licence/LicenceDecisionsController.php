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

        foreach ($messages as $key => $message) {
            if (is_null($message)) {
                unset($messages[$key]);
            }
        }

        $form = $formHelper->createFormWithRequest('LicenceStatusDecisionMessages', $this->getRequest());

        $pageTitle = null;
        switch ($decision) {
            case 'surrender':
            case 'terminate':
                if ($this->getRequest()->isPost() || !$active) {
                    return $this->redirectToDecision($decision, $licence);
                }
                break;
            case 'suspend':
                $pageTitle = "Suspend Licence";
                if ($this->getRequest()->isPost() || empty($messages)) {
                    return $this->redirectToDecision($decision, $licence);
                }
                break;
            case 'curtail':
                $pageTitle = "Curtail Licence";
                if ($this->getRequest()->isPost() || empty($messages)) {
                    return $this->redirectToDecision($decision, $licence);
                }
                break;
            case 'revoke':
                $pageTitle = "Revoke Licence";
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

        return $this->renderView($view, $pageTitle);
    }

    /**
     * Curtail a licence.
     *
     * @return string|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function curtailAction()
    {
        $licenceId = $this->fromRoute('licence');
        $licenceStatus = $this->fromRoute('status', null);
        if (!is_null($licenceStatus)) {
            if ($this->isButtonPressed('remove')) {
                return $this->removeLicenceStatusRule(
                    $licenceId,
                    $licenceStatus,
                    'licence-status.curtailment.message.remove.success'
                );
            }

            $licenceStatus = $this->getStatusForLicenceById($licenceStatus);
        }

        if ($this->isButtonPressed('affectImmediate')) {
            return $this->affectImmediate(
                $licenceId,
                'curtailNow',
                'licence-status.curtailment.message.save.success'
            );
        }

        $form = $this->getDecisionForm(
            'LicenceStatusDecisionCurtail',
            $licenceStatus,
            array(
                'curtailFrom' => 'startDate',
                'curtailTo' => 'endDate'
            )
        );

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
                    ),
                    $licenceStatus
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
        $licenceStatus = $this->fromRoute('status', null);
        if (!is_null($licenceStatus)) {
            if ($this->isButtonPressed('remove')) {
                return $this->removeLicenceStatusRule(
                    $licenceId,
                    $licenceStatus,
                    'licence-status.revocation.message.remove.success'
                );
            }

            $licenceStatus = $this->getStatusForLicenceById($licenceStatus);
        }

        if ($this->isButtonPressed('affectImmediate')) {
            return $this->affectImmediate(
                $licenceId,
                'revokeNow',
                'licence-status.revocation.message.save.success'
            );
        }

        $form = $this->getDecisionForm(
            'LicenceStatusDecisionRevoke',
            $licenceStatus,
            array(
                'revokeFrom' => 'startDate'
            )
        );

        if ($this->getRequest()->isPost()) {
            $form->setData((array)$this->getRequest()->getPost());

            if ($form->isValid()) {
                $formData = $form->getData();

                $this->saveDecisionForLicence(
                    $licenceId,
                    array(
                        'licenceStatus' => LicenceStatusRuleEntityService::LICENCE_STATUS_RULE_REVOKED,
                        'startDate' => $formData['licence-decision']['revokeFrom'],
                    ),
                    $licenceStatus
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
        $licenceStatus = $this->fromRoute('status', null);
        if (!is_null($licenceStatus)) {
            if ($this->isButtonPressed('remove')) {
                return $this->removeLicenceStatusRule(
                    $licenceId,
                    $licenceStatus,
                    'licence-status.suspension.message.remove.success'
                );
            }

            $licenceStatus = $this->getStatusForLicenceById($licenceStatus);
        }

        if ($this->isButtonPressed('affectImmediate')) {
            return $this->affectImmediate(
                $licenceId,
                'suspendNow',
                'licence-status.suspension.message.save.success'
            );
        }

        $form = $this->getDecisionForm(
            'LicenceStatusDecisionSuspend',
            $licenceStatus,
            array(
                'suspendFrom' => 'startDate',
                'suspendTo' => 'endDate'
            )
        );

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
                    ),
                    $licenceStatus
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
                $licenceStatusHelperService->resetToValid($licenceId);

                $this->flashMessenger()->addSuccessMessage('licence-status.reset.message.save.success');

                return $this->redirectToRouteAjax('licence', array('licence' => $licenceId));
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
     * Terminate a licence.
     *
     * @return string|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function terminateAction()
    {
        $licenceId = $this->fromRoute('licence');

        $form = $this->getDecisionForm('LicenceStatusDecisionTerminate');

        if ($this->getRequest()->isPost()) {

            $form->setData((array)$this->getRequest()->getPost());

            if ($form->isValid()) {
                $formData = $form->getData();

                $terminateDate = $formData['licence-decision']['terminateDate'];

                $this->getServiceLocator()->get('Helper\LicenceStatus')
                    ->terminateNow($licenceId, $terminateDate);

                $this->flashMessenger()->addSuccessMessage('licence-status.terminate.message.save.success');

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
     * @param null|array $status Licence status rule.
     * @param null|array $keys Keys to map.
     *
     * @return mixed The form.
     */
    private function getDecisionForm($name = null, $status = null, array $keys = array())
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createFormWithRequest($name, $this->getRequest());

        if (!is_null($status)) {
            return $form->setData(
                $this->formatDataForFormUpdate(
                    array_map(
                        function ($key) use ($status) {
                            return $status[$key];
                        },
                        $keys
                    )
                )
            );
        }

        $form->get('form-actions')->remove('remove');

        return $form;
    }

    /**
     * Save/update a decision against a licence.
     *
     * @param null|int $licenceId The licence id.
     * @param array $data The data to save.
     * @param array|null $statusRule The licence status record.
     */
    private function saveDecisionForLicence($licenceId = null, array $data = array(), $statusRule = null)
    {
        $licenceStatusEntityService = $this->getServiceLocator()->get('Entity\LicenceStatusRule');

        if (!is_null($statusRule)) {
            $data['version'] = $statusRule['version'];
            return $licenceStatusEntityService->updateStatusForLicence(
                $statusRule['id'],
                array(
                    'data' => $data,
                )
            );
        }

        return $licenceStatusEntityService->createStatusForLicence(
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

    /**
     * Get a licence status.
     *
     * @param int $id The licence status id.
     *
     * @return array $validFormData The licence status data for the form.
     */
    private function getStatusForLicenceById($id)
    {
        $licenceStatusEntityService = $this->getServiceLocator()->get('Entity\LicenceStatusRule');
        return $licenceStatusEntityService->getStatusForLicence($id);
    }

    /**
     * Remove the licence status rule record.
     *
     * @param $licence The licence id.
     * @param $licenceStatusId The licence status id.
     * @param $message The message to display.
     *
     * @return mixed
     */
    private function removeLicenceStatusRule($licence, $licenceStatusId, $message)
    {
        $licenceStatusEntityService = $this->getServiceLocator()->get('Entity\LicenceStatusRule');
        $licenceStatusEntityService->removeStatusesForLicence($licenceStatusId);

        $this->flashMessenger()->addSuccessMessage($message);

        return $this->redirectToRouteAjax(
            'licence',
            array(
                'licence' => $licence
            )
        );
    }

    /**
     * Return an array that can be set on the form.
     *
     * @param array $licenceDecision The licence decision data.
     *
     * @return array The formatted data
     */
    private function formatDataForFormUpdate(array $licenceDecision = array())
    {
        return array(
            'licence-decision-affect-immediate' => array(
                'immediateAffect' => 'N',
            ),
            'licence-decision' => $licenceDecision
        );
    }
}
