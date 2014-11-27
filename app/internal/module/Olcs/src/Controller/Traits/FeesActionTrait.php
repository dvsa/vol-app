<?php

/**
 * Fees action trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Traits;

use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Common\Service\Listener\FeeListenerService;
use Common\Service\Entity\FeeEntityService;

/**
 * Fees action trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
trait FeesActionTrait
{
    protected $cardTypes = [
        'fpm_card_online',
        'fpm_card_offline'
    ];

    abstract protected function renderLayout($view);

    /**
     * Common logic when rendering the list of fees
     */
    protected function commonFeesAction($licenceId)
    {
        $this->loadScripts(['forms/filter', 'table-actions', 'fees']);

        $status = $this->params()->fromQuery('status');
        $filters = [
            'status' => $status
        ];

        $table = $this->getFeesTable($licenceId, $status);

        $view = new ViewModel(
            [
                'table' => $table,
                'form'  => $this->getFeeFilterForm($filters)
            ]
        );
        $view->setTemplate('licence/fees/layout');
        return $this->renderLayout($view);
    }

    protected function checkActionRedirect($lvaType)
    {
        if ($this->getRequest()->isPost()) {

            $data = (array)$this->getRequest()->getPost();
            if (!isset($data['id']) || empty($data['id'])) {
                $this->addErrorMessage('Please select at least one item');
                return $this->redirect()->toRoute(null, [], null, true);
            }

            // @NOTE: only one action supported at the moment, so no need to inspect
            // it. Update logic as and when this needs to change...

            $params = [
                'action' => 'pay-fees',
                'fee' => implode(',', $data['id'])
            ];

            return $this->redirect()->toRoute(
                $lvaType . '/fees/fee_action',
                $params,
                null,
                true
            );
        }
    }

    /**
     * Get fee filter form
     *
     * @param array $filters
     * @return Zend\Form\Form
     */
    protected function getFeeFilterForm($filters = [])
    {
        $form = $this->getForm('fee-filter');
        $form->remove('csrf');
        $form->setData($filters);

        return $form;
    }

    /**
     * Get fees table
     *
     * @param string $licenceId
     * @param string $status
     * @return Common\Service\Table\TableBuilder;
     */
    protected function getFeesTable($licenceId, $status)
    {
        switch ($status) {
            case 'historical':
                $feeStatus = sprintf(
                    'IN ["%s","%s","%s"]',
                    FeeEntityService::STATUS_PAID,
                    FeeEntityService::STATUS_WAIVED,
                    FeeEntityService::STATUS_CANCELLED
                );
                break;
            case 'all':
                $feeStatus = "";
                break;
            case 'current':
            default:
                $feeStatus = sprintf(
                    'IN ["%s","%s"]',
                    FeeEntityService::STATUS_OUTSTANDING,
                    FeeEntityService::STATUS_WAIVE_RECOMMENDED
                );
        }
        $params = [
            'licence' => $licenceId,
            'page'    => $this->params()->fromQuery('page', 1),
            'sort'    => $this->params()->fromQuery('sort', 'receivedDate'),
            'order'   => $this->params()->fromQuery('order', 'DESC'),
            'limit'   => $this->params()->fromQuery('limit', 10)
        ];
        if ($feeStatus) {
            $params['feeStatus'] = $feeStatus;
        }

        $feesService = $this->getServiceLocator()->get('Olcs\Service\Data\Fee');
        $results = $feesService->getFees($params, null);

        $tableParams = array_merge($params, ['query' => $this->getRequest()->getQuery()]);
        $table = $this->getTable('fees', $results, $tableParams);

        return $table;
    }

    /**
     * Display fee info and edit waive note
     */
    public function editFeeAction()
    {
        $id = $this->params()->fromRoute('fee', null);
        $feesService = $this->getServiceLocator()->get('Olcs\Service\Data\Fee');
        $fee = $feesService->getFee($id);
        $form = $this->alterFeeForm($this->getForm('fee'), $fee['feeStatus']['id']);
        $form = $this->setDataFeeForm($fee, $form);

        $this->processForm($form);
        if ($this->getResponse()->getContent() !== "") {
            return $this->getResponse();
        }

        $viewParams = [
            'form' => $form,
            'invoiceNo' => $fee['id'],
            'description' => $fee['description'],
            'amount' => $fee['amount'],
            'created' => $fee['invoicedDate'],
            'status' => isset($fee['feeStatus']['description']) ? $fee['feeStatus']['description'] : '',
            'receiptNo' => $fee['receiptNo'],
            'receivedAmount' => $fee['receivedAmount'],
            'receivedDate' => $fee['receivedDate'],
            'paymentMethod' => isset($fee['paymentMethod']['description']) ? : '',
            'processedBy' => isset($fee['lastModifiedBy']['name']) ? $fee['lastModifiedBy']['name'] : ''
        ];
        $view = new ViewModel($viewParams);
        $view->setTemplate('licence/fees/edit-fee');

        return $this->renderView($view, 'No # ' . $fee['id']);
    }

    public function payFeesAction()
    {
        $ids = explode(',', $this->params('fee'));

        $fees = [];
        $maxAmount = 0;
        foreach ($ids as $id) {
            $fees[] = $fee = $this->getServiceLocator()
                ->get('Entity\Fee')
                ->getOverview($id);

            $maxAmount += $fee['amount'];
        }

        $form = $this->getForm('FeePayment');

        $form->get('details')
            ->get('maxAmount')
            ->setValue('£' . number_format($maxAmount, 2));

        $form->getInputFilter()
            ->get('details')
            ->get('received')
            ->getValidatorChain()
            ->addValidator(
                new \Zend\Validator\LessThan(
                    [
                        'max' => $maxAmount,
                        'inclusive' => true
                    ]
                )
            );

        $this->loadScripts(['forms/fee-payment']);

        if ($this->getRequest()->isPost()) {
            $details = $this->getRequest()->getPost('details');
            if (isset($details['paymentType']) && in_array($details['paymentType'], $this->cardTypes)) {
                $this->getServiceLocator()
                    ->get('Helper\Form')
                    ->remove($form, 'details->received');
            }
        }

        $this->formPost($form, 'processPayment');

        if ($this->getResponse()->getContent() !== "") {
            return $this->getResponse();
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('form');

        $title = 'Pay fee';
        if (count($fees) !== 1) {
            $title .= 's';
        }
        return $this->renderView($view, $title);
    }

    /**
     * Alter fee form
     *
     * @param Zend\Form\Form $form
     * @return Zend\Form\Form
     */
    protected function alterFeeForm($form, $status)
    {
        switch ($status) {
            case FeeEntityService::STATUS_OUTSTANDING:
                $form->get('form-actions')->remove('approve');
                $form->get('form-actions')->remove('reject');
                break;

            case FeeEntityService::STATUS_WAIVE_RECOMMENDED:
                $form->get('form-actions')->remove('recommend');
                break;

            case FeeEntityService::STATUS_WAIVED:
                $form->remove('form-actions');
                $form->get('fee-details')->get('waiveReason')->setAttribute('disabled', 'disabled');
                break;

            case FeeEntityService::STATUS_WAIVED:
            case FeeEntityService::STATUS_CANCELLED:
                $form = null;
                break;
        }
        return $form;
    }

    /**
     * Process form
     *
     * @param Zend\Form\Form $form
     */
    protected function processForm($form)
    {
        if ($this->isButtonPressed('recommend')) {
            $this->formPost($form, 'recommendWaive');
        } elseif ($this->isButtonPressed('reject')) {
            $this->validateForm = false;
            $this->formPost($form, 'rejectWaive');
        } elseif ($this->isButtonPressed('approve')) {
            $this->formPost($form, 'approveWaive');
        } elseif ($this->isButtonPressed('cancel')) {
            $this->redirectToList();
        }
    }

    /**
     * Recommend waive
     *
     * @param array $data
     */
    protected function recommendWaive($data)
    {
        $params = [
            'id' => $data['fee-details']['id'],
            'version' => $data['fee-details']['version'],
            'waiveReason' => $data['fee-details']['waiveReason'],
            // changing fee status to waive recommended
            'feeStatus' => 'lfs_wr'
        ];
        $this->updateFeeAndRedirectToList($params);
    }

    /**
     * Reject waive
     *
     * @param array $data
     */
    protected function rejectWaive($data)
    {
        $params = [
            'id' => $data['fee-details']['id'],
            'version' => $data['fee-details']['version'],
            // changing fee status back to outstanding
            'feeStatus' => 'lfs_ot',
        ];
        $message = 'The fee waive recommendation has been rejected';
        $this->updateFeeAndRedirectToList($params, $message);
    }

    /**
     * Approve waive
     *
     * @param array $data
     */
    protected function approveWaive($data)
    {
        $params = [
            'id' => $data['fee-details']['id'],
            'version' => $data['fee-details']['version'],
            'waiveReason' => $data['fee-details']['waiveReason'],
            // changing fee status to waived
            'feeStatus' => 'lfs_w'
        ];

        $this->getServiceLocator()->get('Entity\Fee')->save($params);
        $this->getServiceLocator()->get('Helper\FlashMessenger')
            ->addSuccessMessage('The selected fee has been waived');

        $this->getServiceLocator()->get('Listener\Fee')->trigger(
            $data['fee-details']['id'],
            FeeListenerService::EVENT_WAIVE
        );

        $this->redirectToList();
    }

    /**
     * Update fee and redirect to list
     *
     * @param array $data
     * @param string $message
     */
    protected function updateFeeAndRedirectToList($data, $message = '')
    {
        $this->getServiceLocator()->get('Entity\Fee')->save($data);
        if ($message) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage($message);
        }
        $this->redirectToList();
    }

    /**
     * Set data
     *
     * @param array $fee
     * @param Zend\Form\Form $form
     * @return Zend\Form\Form
     */
    protected function setDataFeeForm($fee, $form)
    {
        if ($form) {
            $form->get('fee-details')->get('id')->setValue($fee['id']);
            $form->get('fee-details')->get('version')->setValue($fee['version']);
            $form->get('fee-details')->get('waiveReason')->setValue($fee['waiveReason']);
        }
        return $form;
    }

    /**
     * Redirect back to list of fees
     *
     * @return redirect
     */
    protected function redirectToList()
    {
        $licenceId = $this->getFromRoute('licence');
        if ($licenceId) {
            $route = 'licence/fees';
            $params = ['licence' => $licenceId];
        } else {
            $applicationId = $this->getFromRoute('application');
            $route = 'lva-application/fees';
            $params = ['application' => $applicationId];
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            $data = ['status' => 302, 'location' => $this->url()->fromRoute($route, $params)];
            $this->getResponse()->getHeaders()->addHeaders(['Content-Type' => 'application/json']);
            $this->getResponse()->setContent(Json::encode($data));
            return;
        }
        $this->redirect()->toRoute($route, $params);
    }

    protected function processPayment($data)
    {
        return $this->redirectToList();
    }
}
