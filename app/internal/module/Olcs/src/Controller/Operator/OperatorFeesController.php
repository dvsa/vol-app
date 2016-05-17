<?php

/**
 * Operator Fees Controller
 */
namespace Olcs\Controller\Operator;

use Common\Controller\Traits\GenericReceipt;
use Olcs\Controller\Traits\FeesActionTrait;

/**
 * Operator Fees Controller
 */
class OperatorFeesController extends OperatorController
{
    use FeesActionTrait,
        GenericReceipt;

    /**
     * @var string
     */
    protected $section = 'fees';

    /**
     * @var string
     */
    protected $subNavRoute = 'operator_fees';

    /**
     * Route (prefix) for fees action redirects
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return string
     */
    protected function getFeesRoute()
    {
        return 'operator/fees';
    }

    /**
     * The fees route redirect params
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getFeesRouteParams()
    {
        return [
            'organisation' => $this->params()->fromRoute('organisation'),
        ];
    }

    /**
     * The controller specific fees table params
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getFeesTableParams()
    {
        return [
            'organisation' => $this->params()->fromRoute('organisation'),
            'status' => 'current',
        ];
    }

    protected function renderLayout($view)
    {
        return $this->renderView($view);
    }

    protected function getFeeTypeDtoData()
    {
        return ['organisation' => $this->params()->fromRoute('organisation')];
    }

    /**
     * Alter create fee form
     *
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    protected function alterCreateFeeForm($form)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        // disable amount validation by default
        $formHelper->disableEmptyValidationOnElement($form, 'fee-details->amount');
        $form->get('fee-details')->get('amount')->setAttribute('readonly', true);

        // populate fee type select
        $options = $this->fetchFeeTypeValueOptions();
        $form->get('fee-details')->get('feeType')->setValueOptions($options);

        $post = $this->getRequest()->getPost()->toArray();
        // populate GV Permit and PSV Auth dropdowns
        $currentFeeType = isset($post['fee-details']['feeType']) ? $post['fee-details']['feeType'] : null;
        $data = $this->fetchFeeTypeListData(null, $currentFeeType);

        if (isset($data['extra']['valueOptions']['irfoGvPermit'])) {
            $form->get('fee-details')->get('irfoGvPermit')->setValueOptions(
                $data['extra']['valueOptions']['irfoGvPermit']
            );
        }

        if (isset($data['extra']['valueOptions']['irfoPsvAuth'])) {
            $form->get('fee-details')->get('irfoPsvAuth')->setValueOptions(
                $data['extra']['valueOptions']['irfoPsvAuth']
            );
        }
        if ($this->getRequest()->isPost() && !$data['extra']['showQuantity'] && $currentFeeType) {
            $formHelper->remove($form, 'fee-details->quantity');
        }

        return $form;
    }

    /**
     * Get create fee dto data
     *
     * @param $formData
     * @return array
     */
    protected function getCreateFeeDtoData($formData)
    {
        $params = [
            'invoicedDate' => $formData['fee-details']['createdDate'],
            'feeType' => $formData['fee-details']['feeType'],
            'irfoGvPermit' => $formData['fee-details']['irfoGvPermit'],
            'irfoPsvAuth' => $formData['fee-details']['irfoPsvAuth']
        ];
        if (isset($formData['fee-details']['quantity'])) {
            $params['quantity'] = $formData['fee-details']['quantity'];
        }
        return $params;
    }
}
