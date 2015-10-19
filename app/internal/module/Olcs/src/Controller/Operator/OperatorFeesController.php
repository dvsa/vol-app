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
}
