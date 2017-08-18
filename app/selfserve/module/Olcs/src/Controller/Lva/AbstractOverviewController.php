<?php

namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\RefData;
use Dvsa\Olcs\Transfer\Command\Application\CancelApplication as CancelApplicationCmd;
use Dvsa\Olcs\Transfer\Command\Application\WithdrawApplication as WithdrawApplicationCmd;
use Dvsa\Olcs\Transfer\Query\Application\Application as ApplicationQry;
use Zend\Mvc\MvcEvent;

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

    /**
     * Process action : Index
     *
     * @return \Olcs\View\Model\LvaOverview
     */
    public function indexAction()
    {
        $applicationId = $this->getApplicationId();

        $data = $this->getOverviewData($applicationId);
        $data['idIndex'] = $this->getIdentifierIndex();

        $isVisible = ($data['status']['id'] === RefData::APPLICATION_STATUS_NOT_SUBMITTED);

        $sections = $this->getSections($data);

        $form = null;
        if ($isVisible) {
            /** @var \Common\Form\Form $form */
            $form = $this->getServiceLocator()->get('FormServiceManager')
                ->get('lva-' . $this->lva . '-overview-submission')
                ->getForm(
                    $data,
                    [
                        'sections' => $sections,
                        'isReadyToSubmit' => $this->isReadyToSubmit($sections),
                        'actionUrl' => $this->url()->fromRoute(
                            'lva-' . $this->lva . '/pay-and-submit',
                            [$this->getIdentifierIndex() => $applicationId]
                        ),
                    ]
                )
                ->setData($data);
        }

        return $this->getOverviewView($data, $sections, $form);
    }

    /**
     * Process action : Cancel
     *
     * @return \Common\View\Model\Section|\Zend\Http\Response
     */
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

    /**
     * Process action : Withdraw
     *
     * @return \Common\View\Model\Section|\Zend\Http\Response
     */
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

    /**
     * Check for redirect after CRUD
     *
     * @param int $lvaId LVA identifier
     *
     * @return null|\Zend\Http\Response
     */
    protected function checkForRedirect($lvaId)
    {
        if ($this->isButtonPressed('cancel') &&
            ($this->params('action') === 'cancel' || $this->params('action') === 'withdraw')) {
            return $this->redirect()->toRoute('lva-' . $this->lva, [], [], true);
        }
        return parent::checkForRedirect($lvaId);
    }

    /**
     * Return Api data for specified application
     *
     * @param int $applicationId Application Identifier
     *
     * @return array|mixed
     */
    protected function getOverviewData($applicationId)
    {
        $dto = ApplicationQry::create(['id' => $applicationId, 'validateAppCompletion' => true]);
        $response = $this->handleQuery($dto);

        return $response->getResult();
    }

    /**
     * Return view
     *
     * @param array                    $data     Api/Form Data
     * @param array                    $sections Sections
     * @param \Zend\Form\FormInterface $form     Form
     *
     * @return \Olcs\View\Model\LvaOverview
     */
    abstract protected function getOverviewView($data, $sections, $form);

    /**
     * Return available sections
     *
     * @param array $data Api/Form data
     *
     * @return array
     */
    abstract protected function getSections($data);

    /**
     * Is Application\Variation Ready to submbit
     *
     * @param array $sections Sections Array
     *
     * @return boolean
     */
    abstract protected function isReadyToSubmit($sections);
}
