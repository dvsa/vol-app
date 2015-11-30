<?php

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application\Fees;

use Common\Controller\Traits\GenericReceipt;
use Olcs\Controller\Application\ApplicationController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits\FeesActionTrait;
use Zend\View\Model\ViewModel;

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationFeesController extends ApplicationController implements LeftViewProvider
{
    use FeesActionTrait,
        GenericReceipt;

    protected function renderLayout($view)
    {
        return $this->render($view);
    }

    /**
     * Route (prefix) for fees action redirects
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return string
     */
    protected function getFeesRoute()
    {
        return 'lva-application/fees';
    }

    /**
     * The fees route redirect params
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getFeesRouteParams()
    {
        return [
            'application' => $this->getFromRoute('application')
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
            'licence' => $this->getLicenceIdForApplication(),
            'status' => 'current',
        ];
    }

    protected function getFeeTypeDtoData()
    {
        return ['application' => $this->params()->fromRoute('application')];
    }

    protected function getCreateFeeDtoData($formData)
    {
        return [
            'invoicedDate' => $formData['fee-details']['createdDate'],
            'feeType' => $formData['fee-details']['feeType'],
            'application' => $this->params()->fromRoute('application'),
        ];
    }
}
