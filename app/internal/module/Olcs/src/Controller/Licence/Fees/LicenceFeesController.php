<?php

/**
 * Licence Fees Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Licence\Fees;

use Common\Controller\Traits\GenericReceipt;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Licence\LicenceController;
use Olcs\Controller\Traits\FeesActionTrait;
use \Zend\View\Model\ViewModel;

/**
 * Licence Fees Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceFeesController extends LicenceController implements LeftViewProvider
{
    use FeesActionTrait,
        GenericReceipt;

    /**
     * render layout
     *
     * @param ViewModel $view view
     *
     * @return ViewModel
     */
    protected function renderLayout($view)
    {
        $tmp = $this->getViewWithLicence($view->getVariables());
        $view->setVariables($tmp->getVariables());

        return $this->renderView($view);
    }

    /**
     * getFeesRoute
     * Route (prefix) for fees action redirects
     * @see Olcs\Controller\Traits\FeesActionTrait
     *
     * @return string
     */
    protected function getFeesRoute()
    {
        return 'licence/fees';
    }

    /**
     * The fees route redirect params
     *
     * see Olcs\Controller\Traits\FeesActionTrait
     *
     * @return array
     */
    protected function getFeesRouteParams()
    {
        return [
            'licence' => $this->params()->fromRoute('licence')
        ];
    }

    /**
     * The controller specific fees table params
     *
     * @see Olcs\Controller\Traits\FeesActionTrait
     *
     * @return array
     */
    protected function getFeesTableParams()
    {
        return [
            'licence' => $this->params()->fromRoute('licence'),
            'status' => 'current',
        ];
    }

    /**
     * getFeetypeDtoData
     *
     * @return array
     */
    protected function getFeeTypeDtoData()
    {
        return ['licence' => $this->params()->fromRoute('licence')];
    }

    /**
     * getCreateFeeDtoData
     *
     * @param array $formData formData
     *
     * @return array
     */
    protected function getCreateFeeDtoData($formData)
    {
        return [
            'invoicedDate' => $formData['fee-details']['createdDate'],
            'feeType' => $formData['fee-details']['feeType'],
            'licence' => $this->params()->fromRoute('licence'),
        ];
    }
}
