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
        $licenceId = $this->params()->fromRoute('licence');
        if (!$licenceId) {
            $applicationId = $this->params()->fromRoute('applicationId');
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
        $params = [
            'licence' => $licenceId,
            'feeStatus' => "IN ('lfs_ot', 'lfs_wr')",
            'page'    => $this->params()->fromRoute('page', 1),
            'sort'    => $this->params()->fromRoute('sort', 'receivedDate'),
            'order'   => $this->params()->fromRoute('order', 'DESC'),
            'limit'   => $this->params()->fromRoute('limit', 10),
        ];

        $feesService = $this->getServiceLocator()->get('Olcs\Service\Data\Fee');

        $results = $feesService->getFees($params, null);

        $table = $this->getTable('fees', $results, $params);
        $view = $this->getViewWithLicence(['table' => $table]);
        $view->setTemplate('licence/fees');

        if ($applicationId) {
            $applicationJourneyHelper = $this->getServiceLocator()->get('ApplicationJourneyHelper');
            $renderedView = $applicationJourneyHelper->render($view, $applicationId);
        } else {
            $renderedView = $this->renderView($view);
        }

        return $renderedView;
    }
}
