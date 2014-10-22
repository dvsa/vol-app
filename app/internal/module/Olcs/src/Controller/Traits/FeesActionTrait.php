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
        }
        $params = [
            'licence' => $licenceId,
            'page'    => $this->params()->fromRoute('page', 1),
            'sort'    => $this->params()->fromRoute('sort', 'id'),
            'order'   => $this->params()->fromRoute('order', 'desc'),
            'limit'   => $this->params()->fromRoute('limit', 10),
        ];

        $feesService = $this->getServiceLocator()->get('Olcs\Service\Data\Fee');
        $filters = [
            'feeStatus' => ['lfs_ot', 'lfs_wr']
        ];

        /*
         * we need to present filtered and paginated table so we should
         * fetch all data at once and paginate it on the table layer
         */
        $serviceParams = $params;
        $serviceParams['page'] = 1;
        $serviceParams['limit'] = self::MAX_LICENCE_FEES;
                
        $results = $feesService->getFees($serviceParams, null, $filters);
        
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
