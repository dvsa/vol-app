<?php

/**
 * BusProcessingDecisionController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Processing;

use Common\Controller\CrudInterface;

/**
 * BusProcessingDecisionController
 *
 * author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusProcessingDecisionController extends BusProcessingController implements CrudInterface
{
    protected $identifierName = 'busRegId';
    protected $service = 'BusReg';

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'fields'
            )
        )
    );

    /**
     * Index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $service = $this->getServiceLocator()->get('DataServiceManager')->get('Common\Service\Data\BusReg');

        $view = $this->getViewWithBusReg();
        $busReg = $this->getBusReg();
        $newVariationCancellation = $this->getNewVariationCancellationStatuses();
        $rejectedStatuses = $this->getRejectedStatuses();

        //get statuses from ref data
        $refData = $this->getServiceLocator()->get('Common\Service\Data\RefData');
        $allStatus = $refData->fetchListOptions('bus_reg_status');

        if (in_array($busReg['status']['id'], $rejectedStatuses)) {
            switch ($busReg['status']['id']) {
                case 'breg_s_refused':
                    if ($busReg['shortNoticeRefused'] == 'Y') {
                        $reason = $busReg['reasonSnRefused'];
                    } else {
                        $reason = $busReg['reasonRefused'];
                    }

                    break;
                case 'breg_s_cancelled':
                case 'breg_s_admin':
                    $reason = $busReg['reasonCancelled'];
                    break;
                case 'breg_s_withdrawn':
                    $reason = $busReg['withdrawnReason']['description'];
            }

            $data = [
                'decision' => $allStatus[$busReg['status']['id']],
                'reason' => $reason,
                'statusId' => $busReg['status']['id']
            ];

            $view->setVariable('decisionData', $data);
        } elseif (in_array($busReg['status']['id'], $newVariationCancellation)
            || $busReg['status']['id'] == 'breg_s_registered') {
            $view->setVariable('noDecisionStatuses', $newVariationCancellation);
            $view->setVariable('busReg', $busReg);
            $view->setVariable('isGrantable', $service->isGrantable($busReg['id']));
        }

        $view->setTemplate('pages/bus/processing-decision');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        return $this->renderView($view);
    }

    /**
     * Resets the record to the previous status
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function resetAction()
    {
        $busReg = $this->getBusReg();
        $service = $this->getServiceLocator()->get('DataServiceManager')->get('Common\Service\Data\BusReg');

        //flip the statuses
        $data = [
            'id' => $busReg['id'],
            'status' => $busReg['revertStatus']['id'],
            'revertStatus' => $busReg['status']['id'],
            'version' => $busReg['version']
        ];

        $service->save($data);
        return $this->redirectToIndex();
    }

    /**
     * Action to grant a bus registration
     *
     * @return bool|mixed|\Zend\Stdlib\ResponseInterface|\Zend\View\Model\ViewModel
     */
    public function grantAction()
    {
        $view = $this->getViewWithBusReg();

        $busRegId = $this->params()->fromRoute('busRegId');
        $busReg = $this->getBusReg();
        $service = $this->getServiceLocator()->get('DataServiceManager')->get('Common\Service\Data\BusReg');

        if (!$service->isGrantable($busRegId)) {
            return false; //shouldn't happen as button will be hidden!
        } else {
            switch ($busReg['status']['id']) {
                case 'breg_s_new':
                case 'breg_s_cancellation':
                    $data = [
                        'id' => $busReg['id'],
                        'status' =>
                            ($busReg['status']['id'] == 'breg_s_new' ? 'breg_s_registered' : 'breg_s_cancelled'),
                        'revertStatus' => $busReg['status']['id'],
                        'version' => $busReg['version']
                    ];

                    $service->save($data);
                    return $this->redirectToIndex();
                case 'breg_s_var':
                    $form = $this->generateFormWithData(
                        'BusRegVariationReason',
                        'processGrantVariation',
                        $this->getDataForForm()
                    );

                    if ($this->getIsSaved()) {
                        return $this->getResponse();
                    }

                    $this->setPlaceholder('form', $form);

                    $view->setTemplate('pages/crud-form');

                    return $this->renderView($view);
                default:
                    //throw exception
            }
        }
    }

    /**
     * Action for a change of bus reg status
     *
     * @return \Zend\Stdlib\ResponseInterface|\Zend\View\Model\ViewModel
     */
    public function statusAction()
    {
        $status = $this->params()->fromRoute('status');

        switch ($status) {
            case 'breg_s_withdrawn':
                $form = 'BusRegUpdateWithdrawn';
                break;
            default:
                $form = 'BusRegUpdateStatus';
        }

        $form = $this->generateFormWithData($form, 'processUpdateStatus', $this->getDataForForm());

        if ($this->getIsSaved()) {
            return $this->getResponse();
        }

        $view = $this->getView();

        $this->setPlaceholder('form', $form);

        $view->setTemplate('pages/crud-form');

        return $this->renderView($view);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function processGrantVariation($data)
    {
        $busReg = $this->getBusReg();

        $data['fields']['revertStatus'] = $busReg['status']['id'];
        $data['fields']['status'] = 'breg_s_registered';
        $data['fields']['id'] = $busReg['id'];
        $data['fields']['version'] = $busReg['version'];

        parent::processSave($data, false);

        return $this->redirectToIndex();
    }

    /**
     * Process a status update - there are 7 or 8 more stories around this so we'll eventually use a
     * service for all of them
     *
     * @param $data
     * @return mixed
     */
    public function processUpdateStatus($data)
    {
        $busReg = $this->getBusReg();

        $data['fields']['revertStatus'] = $busReg['status']['id'];
        $data['lastModifiedBy'] = $this->getLoggedInUser();

        switch ($data['fields']['status']) {
            case 'breg_s_admin':
                $data['fields']['reasonCancelled'] = $data['fields']['reason'];
                break;
            case 'breg_s_refused':
                $data['fields']['reasonRefused'] = $data['fields']['reason'];
                break;
            case 'breg_s_withdrawn':
                $data['fields']['withdrawnReason'] = $data['fields']['reason'];
                break;
            case 'sn_refused':
                $data = $this->processShortNotice($data);
                break;
        }

        parent::processSave($data, false);

        return $this->redirectToIndex();
    }

    /**
     * Processes a refusal by short notice
     *
     * @param array $data
     * @param null $busReg
     * @return array
     */
    public function processShortNotice($data, $busReg = null)
    {
        if (is_null($busReg)) {
            $busReg = $this->getBusReg();
        }

        $noticePeriodService = $this->getServiceLocator()->get('Common\Service\ShortNotice');

        //this isn't an actual status so the status stays the same
        $data['fields']['status'] = $busReg['status']['id'];
        $data['fields']['reasonSnRefused'] = $data['fields']['reason'];
        $data['fields']['isShortNotice'] = 'N';
        $data['fields']['shortNoticeRefused'] = 'Y';
        $data['fields']['effectiveDate'] = $noticePeriodService->calculateNoticeDate($busReg);

        $user = $this->getLoggedInUser();

        $shortNoticeFields = [
            'bankHolidayChange' => 'N',
            'unforseenChange' => 'N',
            'unforseenDetail' => null,
            'timetableChange' => 'N',
            'timetableDetail' => null,
            'replacementChange' => 'N',
            'replacementDetail' => null,
            'notAvailableChange' => 'N',
            'notAvailableDetail' => null,
            'specialOccasionChange' => 'N',
            'specialOccasionDetail' => null,
            'connectionChange' => 'N',
            'connectionDetail' => null,
            'holidayChange' => 'N',
            'holidayDetail' => null,
            'trcChange' => 'N',
            'trcDetail' => null,
            'policeChange' => 'N',
            'policeDetail' => null,
            'createdBy' => $user,
            'lastModifiedBy' => $user,
        ];

        $service = $this->getServiceLocator()->get('DataServiceManager')->get('Generic\Service\Data\BusShortNotice');
        $shortNotice = $service->fetchList(['busReg' => $busReg['id']]);

        if (isset($shortNotice[0])) {
            $record = $shortNotice[0];
            unset($record['lastModifiedOn']);
        } else {
            $record = [
                'busReg' => $busReg['id'],
            ];
        }

        foreach ($shortNoticeFields as $field => $defaultFieldValue) {
            $record[$field] = $defaultFieldValue;
        }

        $service->save($record);

        return $data;
    }

    /**
     * Get data for form
     *
     * @return array
     */
    public function getDataForForm()
    {
        $data = parent::getDataForForm();

        $data['fields']['status'] = $this->getFromRoute('status');

        return $data;
    }

    /**
     * Redirects to the index page
     *
     * @return mixed
     */
    public function redirectToIndex()
    {
        $busReg = $this->getFromRoute('busRegId');

        return $this->redirect()->toRouteAjax(
            null,
            ['action'=>'index', 'busRegId' => $busReg, 'status' => null],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }
}
