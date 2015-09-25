<?php

/**
 * Bus Fees Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Fees;

use Olcs\Controller\Bus\BusController;
use Olcs\Controller\Traits;

/**
 * Bus Fees Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusFeesController extends BusController
{
    use Traits\FeesActionTrait;

    protected $section = 'fees';
    protected $subNavRoute = 'licence_bus_fees';

    /**
     * Route (prefix) for fees action redirects
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return string
     */
    protected function getFeesRoute()
    {
        return 'licence/bus-fees';
    }

    /**
     * The fees route redirect params
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

    protected function renderLayout($view)
    {
        return $this->renderView($view);
    }

    public function redirectToIndex()
    {
        return $this->redirectToList();
    }
}
