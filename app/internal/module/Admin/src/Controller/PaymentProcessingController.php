<?php
/**
 * Payment Processing Controller
 */

namespace Admin\Controller;

use Common\Controller\AbstractActionController;
use Olcs\Controller\Traits\FeesActionTrait;

/**
 * Payment Processing Controller
 */
class PaymentProcessingController extends AbstractActionController
{
    use FeesActionTrait;

    protected function alterFeeTable($table)
    {
        // no-op
        return $table;
    }

    /**
     * Route (prefix) for fees action redirects
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return string
     */
    protected function getFeesRoute()
    {
        return 'admin-dashboard/admin-payment-processing/misc-fees';
    }

    /**
     * The fees route redirect params
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getFeesRouteParams()
    {
        return [];
    }

    /**
     * The controller specific fees table params
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getFeesTableParams()
    {
        return [
            'isMiscellaneous' => 1,
            'status' => 'current',
        ];
    }

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-payment-processing';


    /**
     * Index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        return $this->feesAction('partials/table');
    }

    protected function renderLayout($view, $pageTitle = null, $pageSubTitle = null)
    {
        // This is a zend\view\variables object - cast it to an array.
        $layout = $this->getView((array)$view->getVariables());

        $this->getViewHelperManager()->get('placeholder')->getContainer('tableFilters')
            ->set($view->getVariable('form'));

        $layout->setTemplate('layout/admin-payment-processing-section');
        $layout->addChild($view, 'content');
        return parent::renderView($layout, 'Payment processing', $pageSubTitle);
    }


    /**
     * Redirect action
     *
     * @return \Zend\Http\Response
     */
    public function redirectAction()
    {
        return $this->redirectToRouteAjax(
            'admin-dashboard/admin-payment-processing/misc-fees',
            ['action'=>'index'],
            ['code' => '303'],
            true
        );
    }
}
