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

    /**
     * render Layout
     *
     * @param string $view      view
     * @param null   $pageTitle pageTitle
     *
     * @return mixed
     */
    protected function renderLayout($view, $pageTitle = null)
    {
        return $this->render($view, $pageTitle);
    }

    /**
     * Route (prefix) for fees action redirects
     *
     * @see Olcs\Controller\Traits\FeesActionTrait
     *
     * @return string
     */
    protected function getFeesRoute()
    {
        return 'lva-application/fees';
    }

    /**
     * The fees route redirect params
     *
     * @see Olcs\Controller\Traits\FeesActionTrait
     *
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
     *
     * @see Olcs\Controller\Traits\FeesActionTrait
     *
     * @return array
     */
    protected function getFeesTableParams()
    {
        return [
            'licence' => $this->getLicenceIdForApplication(),
            'status' => 'current',
        ];
    }

    /**
     * fee type D to data get Method
     *
     * @return array
     */
    protected function getFeeTypeDtoData()
    {
        return ['application' => $this->params()->fromRoute('application')];
    }

    /**
     * Create Fee D to Data get method
     *
     * @param string $formData formdata
     *
     * @return array
     */
    protected function getCreateFeeDtoData($formData)
    {
        return [
            'invoicedDate' => $formData['fee-details']['createdDate'],
            'feeType' => $formData['fee-details']['feeType'],
            'application' => $this->params()->fromRoute('application'),
        ];
    }
}
