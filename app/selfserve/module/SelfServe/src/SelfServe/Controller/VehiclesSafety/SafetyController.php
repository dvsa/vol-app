<?php

/**
 * SafetyController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\VehiclesSafety;

use Common\Controller\FormJourneyActionController;
use Zend\View\Model\ViewModel;

/**
 * SafetyController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SafetyController extends FormJourneyActionController
{
    /**
     * Safety form
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $applicationId = $this->params()->fromRoute('applicationId');

        $bundle = array(
            'properties' => array(
                'version',
                'safetyConfirmation'
            ),
            'children' => array(
                'licence' => array(
                    'properties' => array(
                        'id',
                        'version',
                        'safetyInsVehicles',
                        'safetyInsTrailers',
                        'safetyInsVaries',
                        'tachographIns',
                        'tachographInsName'
                    )
                )
            )
        );

        $data = $this->makeRestCall('Application', 'GET', array('id' => $applicationId), $bundle);

        $form = $this->generateFormWithData(
            'vehicle-safety',
            'processVehicleSafety',
            array('data' => $this->formatDataForForm($data)),
            true
        );

        $view = $this->getViewModel(['form' => $form]);
        $view->setTemplate('self-serve/vehicle-safety/safety');

        return $view;
    }

    /**
     * Format data for the form
     *
     * @param array $data
     * @return array
     */
    protected function formatDataForForm($data)
    {
        $data['id'] = $applicationId;

        foreach ($data['licence'] as $key => $val) {
            $data['licence.' . $key] = $val;
        }

        unset($data['licence']);

        $data['safetyConfirmation'] = (
            !isset($data['safetyConfirmation']) || $data['safetyConfirmation'] == 0
            ? array()
            : array('1')
        );

        $data['licence.safetyInsVehicles'] = 'inspection_interval_vehicle.' . $data['licence.safetyInsVehicles'];

        $data['licence.safetyInsTrailers'] = 'inspection_interval_trailer.' . $data['licence.safetyInsTrailers'];

        $data['licence.tachographIns'] = 'tachograph_analyser.' . $data['licence.tachographIns'];

        return $data;
    }

    /**
     * Process the vehicle safety form
     *
     * @param array $data
     */
    protected function processVehicleSafety($data)
    {
        $applicationId = $this->params()->fromRoute('applicationId');

        $applicationData = array(
            'id' => $applicationId,
            'version' => $data['data']['version'],
            'safetyConfirmation' => $data['data']['safetyConfirmation'][0]
        );

        $licenceData = array();

        foreach ($data['data'] as $key => $value) {

            if (strstr($key, 'licence.')) {

                $licenceData[str_replace('licence.', '', $key)] = $value;
            }
        }

        $licenceData['safetyInsVehicles'] = str_replace(
            'inspection_interval_vehicle.',
            '',
            $licenceData['safetyInsVehicles']
        );

        $licenceData['safetyInsTrailers'] = str_replace(
            'inspection_interval_trailer.',
            '',
            $licenceData['safetyInsTrailers']
        );

        $licenceData['tachographIns'] = str_replace('tachograph_analyser.', '', $licenceData['tachographIns']);

        $this->makeRestCall('Application', 'PUT', $applicationData);

        $this->makeRestCall('Licence', 'PUT', $licenceData);

        // @todo Redirect to previous history when this page is created
        return $this->redirect()->toRoute(null, array('applicationId' => $applicationId));
    }

    protected function completeAction()
    {

    }
}
