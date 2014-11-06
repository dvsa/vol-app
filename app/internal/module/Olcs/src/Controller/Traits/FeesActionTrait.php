<?php

/**
 * Fees action trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Traits;

use Zend\View\Model\ViewModel;
use Zend\Json\Json;

/**
 * Fees action trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
trait FeesActionTrait
{
    /**
     * Shows fees table
     */
    public function feesAction()
    {
        $this->loadScripts(['fee-filter', 'table-actions']);

        $licenceId = $this->params()->fromRoute('licence');
        if (!$licenceId) {
            $applicationId = $this->params()->fromRoute('application');
            $bundle = [
                'properties' => null,
                'children' => [
                    'licence' => [
                        'properties' => [
                            'id'
                        ]
                    ]
                ]
            ];
            $results = $this->makeRestCall('Application', 'GET', ['id' => $applicationId], $bundle);
            $licenceId = $results['licence']['id'];
        } else {
            $applicationId = null;
            $this->pageLayout = 'licence';
        }

        $status = $this->params()->fromQuery('status');
        $filters = [
            'status' => $status
        ];

        $table = $this->getFeesTable($licenceId, $status);

        $view = $this->getViewWithLicence(['table' => $table, 'form'  => $this->getFeeFilterForm($filters)]);
        $view->setTemplate('licence/fees');

        if ($applicationId) {
            return $this->render($view);
        } else {
            return $this->renderView($view);
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
                $feeStatus = 'IN ["lfs_pd","lfs_w","lfs_cn"]';
                break;
            case 'all':
                $feeStatus = "";
                break;
            case 'current':
            default:
                $feeStatus = 'IN ["lfs_ot","lfs_wr"]';
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
            'invoiceNo' => $fee['invoiceNo'],
            'description' => $fee['description'],
            'amount' => $fee['amount'],
            'created' => $fee['invoicedDate'],
            'status' => isset($fee['feeStatus']['description']) ? $fee['feeStatus']['description'] : '',
            'receiptNo' => $fee['receiptNo'],
            'receivedAmount' => $fee['receivedAmount'],
            'paymentMethod' => isset($fee['paymentMethod']['description']) ? : '',
            'processedBy' => isset($fee['lastModifiedBy']['name']) ? $fee['lastModifiedBy']['name'] : ''
        ];
        $view = new ViewModel($viewParams);
        $view->setTemplate('licence/fees/edit-fee');

        return $this->renderView($view, 'No # ' . $fee['invoiceNo']);
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
            case 'lfs_ot':
                // outstanding
                $form->get('form-actions')->remove('approve');
                $form->get('form-actions')->remove('reject');
                break;
            case 'lfs_wr':
                // waive recommended
                $form->get('form-actions')->remove('recommend');
                break;
            case 'lfs_w':
                // waived
                $form->remove('form-actions');
                $form->get('fee-details')->get('waiveReason')->setAttribute('disabled', 'disabled');
                break;
            case 'lfs_pd':
                // payed
            case 'lfs_cn':
                // cancelled
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
            'feeStatus' => 'lfs_wr',
            // @TODO change to the current user name when implemented
            'lastModifiedBy' => 2,
            'lastModifiedOn' => date('d-m-Y H:i:s')
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
            'feeStatus' => 'lfs_w',
            // @TODO change to the current user name when implemented
            'lastModifiedBy' => 2,
            'lastModifiedOn' => date('d-m-Y H:i:s')
        ];
        $message = 'The selected fee has been waived';
        $this->updateFeeAndRedirectToList($params, $message);
    }

    /**
     * Update fee and redirect to list
     *
     * @param array $data
     * @param string $message
     */
    protected function updateFeeAndRedirectToList($data, $message = '')
    {
        $feesService = $this->getServiceLocator()->get('Olcs\Service\Data\Fee');
        $feesService->updateFee($data);
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
     * Redirect back to list of tasks
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
}
