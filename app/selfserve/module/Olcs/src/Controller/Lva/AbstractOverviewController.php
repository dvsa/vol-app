<?php

/**
 * Abstract External Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Application\Application as ApplicationQry;
use Dvsa\Olcs\Transfer\Command\Application\CancelApplication as CancelApplicationCmd;
use Dvsa\Olcs\Transfer\Command\Application\WithdrawApplication as WithdrawApplicationCmd;

/**
 * Abstract External Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
abstract class AbstractOverviewController extends AbstractController
{
    protected $lva;
    protected $location = 'external';

    public function indexAction()
    {
        $applicationId = $this->getApplicationId();

        $data = $this->getOverviewData($applicationId);
        $data['idIndex'] = $this->getIdentifierIndex();

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper->createForm('Lva\PaymentSubmission');

        $form->setData($data);

        $sections = $this->getSections($data);

        $enabled = $this->isReadyToSubmit($sections);
        $visible = ($data['status']['id'] == RefData::APPLICATION_STATUS_NOT_SUBMITTED);
        $actionUrl = $this->url()->fromRoute(
            'lva-'.$this->lva.'/pay-and-submit',
            [$this->getIdentifierIndex() => $applicationId]
        );
        $feeAmount = $data['outstandingFeeTotal'];
        $disableCardPayments = (bool) $data['disableCardPayments'];

        $this->getServiceLocator()->get('Helper\PaymentSubmissionForm')
            ->updatePaymentSubmissonForm($form, $actionUrl, $visible, $enabled, $feeAmount, $disableCardPayments);

        return $this->getOverviewView($data, $sections, $form);

    }

    public function cancelAction()
    {
        if ($this->getRequest()->isPost() && $this->isButtonPressed('submit')) {
            $dto = CancelApplicationCmd::create(['id' => $this->params()->fromRoute('application')]);
            $response = $this->handleCommand($dto);

            if (!$response->isOk()) {
                $this->addErrorMessage('unknown-error');
                return $this->redirect()->toRouteAjax('lva-' . $this->lva, [], [], true);
            }

            $this->addSuccessMessage('external.cancel_application.confirm.cancel_message');
            return $this->redirect()->toRouteAjax('dashboard', [], [], true);
        }
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createForm('GenericConfirmation');
        $form->get('form-actions')->get('submit')->setLabel('external.cancel_application.confirm.confirm_button');
        $form->get('form-actions')->get('cancel')->setLabel('external.cancel_application.confirm.back_button');
        $form->get('messages')->get('message')->setValue('external.cancel_application.confirm.message');
        $formHelper->setFormActionFromRequest($form, $this->getRequest());
        return $this->render('cancel_appliction_confirmation', $form);
    }

    public function withdrawAction()
    {
        if ($this->getRequest()->isPost() && $this->isButtonPressed('submit')) {
            $dto = WithdrawApplicationCmd::create(
                [
                    'id' => $this->params()->fromRoute('application'),
                    'reason' => RefData::APPLICATION_WITHDRAW_REASON_WITHDRAWN
                ]
            );
            $response = $this->handleCommand($dto);

            if (!$response->isOk()) {
                $this->addErrorMessage('unknown-error');
                return $this->redirect()->toRouteAjax('lva-' . $this->lva, [], [], true);
            }

            $this->addSuccessMessage('external.withdraw_application.confirm.cancel_message');
            return $this->redirect()->toRouteAjax('dashboard', [], [], true);
        }
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createForm('GenericConfirmation');
        $form->get('form-actions')->get('submit')->setLabel('external.withdraw_application.confirm.confirm_button');
        $form->get('form-actions')->get('cancel')->setLabel('external.withdraw_application.confirm.back_button');
        $form->get('messages')->get('message')->setValue('external.withdraw_application.confirm.message');
        $formHelper->setFormActionFromRequest($form, $this->getRequest());
        return $this->render('withdraw_application_confirmation', $form);
    }

    protected function checkForRedirect($lvaId)
    {
        if ($this->isButtonPressed('cancel') &&
            ($this->params('action') === 'cancel' || $this->params('action') === 'withdraw')) {
            return $this->redirect()->toRoute('lva-' . $this->lva, [], [], true);
        }
        return parent::checkForRedirect($lvaId);
    }

    protected function getOverviewData($applicationId)
    {
        $dto = ApplicationQry::create(['id' => $applicationId]);
        $response = $this->handleQuery($dto);

        return $response->getResult();
    }

    abstract protected function getOverviewView($data, $sections, $form);

    abstract protected function getSections($data);
}
