<?php

/**
 * Payment Processing Fees Controller
 */
namespace Admin\Controller;

use Common\Controller\AbstractActionController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits\FeesActionTrait;
use Zend\View\Model\ViewModel;
use Common\Controller\Traits\GenericReceipt;

/**
 * Payment Processing Fees Controller
 */
class PaymentProcessingFeesController extends AbstractActionController implements LeftViewProvider
{
    use FeesActionTrait,
        GenericReceipt;

    /**
     * @inheritdoc
     */
    protected function alterFeeTable($table, $results)
    {
        // no-op
        return $table;
    }

    /**
     * @inheritdoc
     */
    protected function alterCreateFeeForm($form)
    {
        $options = $this->fetchFeeTypeValueOptions();
        $form->get('fee-details')->get('feeType')->setValueOptions($options);

        // remove IRFO fields
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $formHelper->remove($form, 'fee-details->irfoGvPermit');
        $formHelper->remove($form, 'fee-details->irfoPsvAuth');

        return $form;
    }

    /**
     * Route (prefix) for fees action redirects
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return string
     */
    protected function getFeesRoute()
    {
        return 'admin-dashboard/admin-payment-processing/misc-fees';
    }

    /**
     * The fees route redirect params
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getFeesRouteParams()
    {
        return [];
    }

    /**
     * The controller specific fees table params
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getFeesTableParams()
    {
        return [
            'isMiscellaneous' => 1,
            'status' => 'current',
        ];
    }

    /**
     * Index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        return $this->feesAction();
    }

    /**
     * @inheritdoc
     */
    protected function renderLayout($view, $pageTitle = null, $pageSubTitle = null)
    {
        return parent::renderView($view, 'Payment processing', $pageSubTitle);
    }

    public function getLeftView()
    {
        $status = $this->params()->fromQuery('status');
        $filters = [
            'status' => $status
        ];

        $this->placeholder()->setPlaceholder('tableFilters', $this->getFeeFilterForm($filters));

        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-payment-processing',
                'navigationTitle' => 'Payment Processing'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * @inheritdoc
     */
    protected function maybeClearLeft($layout)
    {
        $this->placeholder()->setPlaceholder('tableFilters', null);
    }

    /**
     * Redirect action
     *
     * @return \Zend\Http\Response
     */
    public function redirectAction()
    {
        return $this->redirectToRouteAjax(
            'admin-dashboard/admin-payment-processing/misc-fees',
            ['action'=>'index'],
            ['code' => '303'],
            true
        );
    }

    protected function getFeeTypeDtoData()
    {
        return ['isMiscellaneous' => 1];
    }

    protected function getCreateFeeDtoData($formData)
    {
        return [
            'user' => $this->getLoggedInUser(),
            'invoicedDate' => $formData['fee-details']['createdDate'],
            'feeType' => $formData['fee-details']['feeType'],
            'amount' => $formData['fee-details']['amount'],
        ];
    }
}
