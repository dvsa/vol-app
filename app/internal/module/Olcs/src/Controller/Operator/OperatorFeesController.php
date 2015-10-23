<?php

/**
 * Operator Fees Controller
 */
namespace Olcs\Controller\Operator;

use Olcs\Controller\Traits;

/**
 * Operator Fees Controller
 */
class OperatorFeesController extends OperatorController
{
    use Traits\FeesActionTrait;

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

        // populate GV Permit and PSV Auth dropdowns
        $data = $this->fetchFeeTypeListData();

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

        return $form;
    }

    protected function getCreateFeeDtoData($formData)
    {
        return [
            'user' => $this->getLoggedInUser(),
            'invoicedDate' => $formData['fee-details']['createdDate'],
            'feeType' => $formData['fee-details']['feeType'],
            'irfoGvPermit' => $formData['fee-details']['irfoGvPermit'],
            'irfoPsvAuth' => $formData['fee-details']['irfoPsvAuth'],
        ];
    }
}
