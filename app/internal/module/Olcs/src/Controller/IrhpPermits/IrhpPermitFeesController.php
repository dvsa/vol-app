<?php

namespace Olcs\Controller\IrhpPermits;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Controller\Traits\GenericReceipt;
use Common\FeatureToggle;
use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\IrhpPermitApplicationControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits\FeesActionTrait;

/**
 * IRHP Fees Controller
 *
 * @author Andy Newton>
 */
class IrhpPermitFeesController extends AbstractController implements
    IrhpPermitApplicationControllerInterface,
    LeftViewProvider,
    ToggleAwareInterface
{
    use FeesActionTrait {
        feesAction as traitFeesAction;
    }
    use GenericReceipt;

    protected $toggleConfig = [
        'default' => [
            FeatureToggle::BACKEND_ECMT
        ],
    ];

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
        return [

            'ecmtPermitApplication' => $this->getFromRoute('permitid'),
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
