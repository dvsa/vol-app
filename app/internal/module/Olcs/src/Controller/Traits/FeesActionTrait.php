<?php

/**
 * Fees action trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Traits;

/**
 * Fees action trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
trait FeesActionTrait
{
    /**
     * Shows fees table
     */
    public function feesAction()
    {
        $this->loadScripts(['fee-filter', 'table-actions']);

        $licenceId = $this->params()->fromRoute('licence');
        if (!$licenceId) {
            $applicationId = $this->params()->fromRoute('application');
            $bundle = [
                'properties' => null,
                'children' => [
                    'licence' => [
                        'properties' => [
                            'id'
                        ]
                    ]
                ]
            ];
            $results = $this->makeRestCall('Application', 'GET', ['id' => $applicationId], $bundle);
            $licenceId = $results['licence']['id'];
        } else {
            $applicationId = null;
            $this->pageLayout = 'licence';
        }

        $status = $this->params()->fromQuery('status');
        $filters = [
            'status' => $status
        ];

        $table = $this->getFeesTable($licenceId, $status);

        $view = $this->getViewWithLicence(['table' => $table, 'form'  => $this->getFeeFilterForm($filters)]);
        $view->setTemplate('licence/fees');

        if ($applicationId) {
            return $this->render($view);
        } else {
            return $this->renderView($view);
        }
    }

    /**
     * Get fee filter form
     *
     * @param array $filters
     * @return Zend\Form\Form
     */
    protected function getFeeFilterForm($filters = [])
    {
        $form = $this->getForm('fee-filter');
        $form->remove('csrf');
        $form->setData($filters);

        return $form;
    }

    /**
     * Get fees table
     *
     * @param string $licenceId
     * @param string $status
     * @return Common\Service\Table\TableBuilder;
     */
    protected function getFeesTable($licenceId, $status)
    {
        switch ($status) {
            case 'historical':
                $feeStatus = "IN ('lfs_pd', 'lfs_w', 'lfs_cn')";
                break;
            case 'all':
                $feeStatus = "";
                break;
            case 'current':
            default:
                $feeStatus = "IN ('lfs_ot', 'lfs_wr')";
        }
        $params = [
            'licence' => $licenceId,
            'page'    => $this->params()->fromQuery('page', 1),
            'sort'    => $this->params()->fromQuery('sort', 'receivedDate'),
            'order'   => $this->params()->fromQuery('order', 'DESC'),
            'limit'   => $this->params()->fromQuery('limit', 10)
        ];
        if ($feeStatus) {
            $params['feeStatus'] = $feeStatus;
        }

        $feesService = $this->getServiceLocator()->get('Olcs\Service\Data\Fee');
        $results = $feesService->getFees($params, null);

        $tableParams = array_merge($params, ['query' => $this->getRequest()->getQuery()]);
        $table = $this->getTable('fees', $results, $tableParams);

        return $table;
    }

    /**
     * No-op that is overridden in the implementing controller
     */
    protected function render($view)
    {
        return $view;
    }
}
