<?php

namespace Olcs\Controller\IrhpPermits;

use Common\Controller\Traits\GenericReceipt;
use Olcs\Controller\Traits\FeesActionTrait;
use Common\Util\IsEcmtId;

/**
 * IRHP Fees Controller
 *
 * @author Andy Newton>
 */
class IrhpPermitFeesController extends AbstractIrhpPermitController
{
    use FeesActionTrait,
        GenericReceipt,
        IrhpFeesTrait;

    /**
     * Route (prefix) for fees action redirects
     *
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return string
     */
    protected function getFeesRoute()
    {
        return 'licence/irhp-fees';
    }

    /**
     * The fees route redirect params
     *
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getFeesRouteParams()
    {
        return [
            'licence' => $this->getFromRoute('licence'),
            'ecmtPermitApplication' => $this->getFromRoute('permitid'),
            'permitid' => $this->getFromRoute('permitid'),

        ];
    }

    /**
     * The controller specific fees table params
     *
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getFeesTableParams()
    {
        $appId = $this->getFromRoute('permitid');
        $attrName = IsEcmtId::isEcmtId($appId) ? 'ecmtPermitApplication' : 'irhpApplication';

        return [

            "$attrName" => $this->getFromRoute('permitid'),
            'status' => 'current',
        ];
    }

    /**
     * Render layout
     *
     * @param string|\Zend\View\Model\ViewModel $view View
     *
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return \Zend\View\Model\ViewModel
     */
    protected function renderLayout($view)
    {
        return $this->renderView($view);
    }

    /**
     * Get fee type dto data
     *
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getFeeTypeDtoData()
    {
        return [
            'ecmtPermitApplication' => $this->getFromRoute('permitid'),
        ];
    }

    /**
     * Get create fee dto data
     *
     * @param array $formData Data
     *
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getCreateFeeDtoData($formData)
    {
        return [
            'invoicedDate' => $formData['fee-details']['createdDate'],
            'feeType' => $formData['fee-details']['feeType'],
            'licence' => $this->params()->fromRoute('licence'),
            'ecmtPermitApplication' => $this->params()->fromRoute('permitid'),
        ];
    }
}
