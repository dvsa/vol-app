<?php

namespace Olcs\Controller\IrhpPermits;

use Common\Controller\Traits\GenericReceipt;
use Olcs\Controller\Traits\FeesActionTrait;

/**
 * IRHP Application Fees Controller
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class IrhpApplicationFeesController extends AbstractIrhpPermitController
{
    use FeesActionTrait {
        feesAction as traitFeesAction;
    }
    use GenericReceipt;

    /**
     * Overidden trait method to set applicable navigation ids for the irhp fees list page
     *
     * @return mixed
     */
    public function feesAction()
    {
        $navigation = $this->getServiceLocator()->get('Navigation');
        $navigation->findOneBy('id', 'licence_irhp_permits-fees')->setActive();
        return $this->traitFeesAction();
    }

    /**
     * Route (prefix) for fees action redirects
     *
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return string
     */
    protected function getFeesRoute()
    {
        return 'licence/irhp-application-fees';
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
            'irhpApplication' => $this->getFromRoute('irhpAppId'),
            'irhpAppId' => $this->getFromRoute('irhpAppId'),
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
        return [
            'irhpApplication' => $this->getFromRoute('irhpAppId'),
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
            'irhpAppId' => $this->getFromRoute('irhpAppId'),
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
    protected function getCreateFeeDtoData(array $formData)
    {
        return [
            'invoicedDate' => $formData['fee-details']['createdDate'],
            'feeType' => $formData['fee-details']['feeType'],
            'licence' => $this->params()->fromRoute('licence'),
            'irhpApplication' => $this->params()->fromRoute('irhpAppId'),
        ];
    }
}
