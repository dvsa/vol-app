<?php
/**
 * Payment Processing Controller
 */

namespace Admin\Controller;

use Common\RefData;
use Zend\View\Model\ViewModel;

use Common\Controller\AbstractActionController;
use Dvsa\Olcs\Transfer\Query\Organisation\CpidOrganisation;
use Dvsa\Olcs\Transfer\Command\Organisation\CpidOrganisationExport;
use Dvsa\Olcs\Transfer\Query\Organisation\Organisation;
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

    public function cpidClassificationAction()
    {
        $params = $this->params();
        $this->loadScripts(['forms/filter', 'table-actions']);

        if ($this->getRequest()->isPost()) {
            if ($params->fromPost('action') === 'Export') {
                $command = CpidOrganisationExport::create(
                    [
                        'cpid' => $this->params()->fromQuery('status', null)
                    ]
                );

                $response = $this->handleCommand($command);
                if ($response->isOk()) {
                    $this->getFlashMessenger()->addSuccessMessage('Mass Export Queued.');

                    return $this->redirectToRouteAjax(
                        'admin-dashboard/admin-payment-processing/cpid-class'
                    );
                }
            }
        }

        $status = (empty($this->params()->fromQuery('status')) ? null : $this->params()->fromQuery('status'));
        $data = [
            'page' => $params->fromQuery('page', 1),
            'limit' => $params->fromQuery('limit', 10)
        ];

        $query = CpidOrganisation::create(
            [
                'cpid' => $status,
                'page' => $data['page'],
                'limit' => $data['limit'],
            ]
        );

        $response = $this->handleQuery($query);
        $table = $this->getTable('admin-cpid-classification', $response->getResult(), $data);

        $cpidFilterForm = $this->getCpidFilterForm($status);

        $view = new ViewModel(
            [
                'table' => $table,
                'form' => $cpidFilterForm
            ]
        );

        $view->setTemplate('partials/table');
        return $this->renderLayout($view);
    }

    /**
     * @inheritdoc
     */
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

    /**
     * Get the CPID filter form.
     *
     * @param $status
     *
     * @return \Common\Controller\type
     */
    private function getCpidFilterForm($status)
    {
        $cpidFilterForm = $this->getForm('cpid-filter');
        $cpidFilterForm->remove('csrf');
        $cpidFilterForm->setData(
            [
                'status' => $status
            ]
        );

        $cpidFilterForm->get('status')->addValueOption(
            [
                RefData::OPERATOR_CPID_ALL => 'All'
            ]
        );

        return $cpidFilterForm;
    }
}
