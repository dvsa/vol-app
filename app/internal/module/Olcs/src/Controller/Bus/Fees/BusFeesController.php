<?php

namespace Olcs\Controller\Bus\Fees;

use Common\Controller\Traits\GenericReceipt;
use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits\FeesActionTrait;

/**
 * Bus Fees Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusFeesController extends AbstractController implements BusRegControllerInterface, LeftViewProvider
{
    use FeesActionTrait,
        GenericReceipt;

    /**
     * Route (prefix) for fees action redirects
     *
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return string
     */
    protected function getFeesRoute()
    {
        return 'licence/bus-fees';
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
            'busRegId' => $this->getFromRoute('busRegId'),
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
            'licence' => $this->getFromRoute('licence'),
            'busReg' => $this->getFromRoute('busRegId'),
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
            'busReg' => $this->getFromRoute('busRegId'),
            'licence' => $this->params()->fromRoute('licence')
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
            'busReg' => $this->params()->fromRoute('busRegId'),
        ];
    }
}
