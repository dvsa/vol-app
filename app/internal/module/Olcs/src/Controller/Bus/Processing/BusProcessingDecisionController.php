<?php

/**
 * BusProcessingDecisionController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Processing;

use Common\Controller\CrudInterface;
use Common\Service\BusRegistration;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * BusProcessingDecisionController
 *
 * author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusProcessingDecisionController extends BusProcessingController implements CrudInterface
{
    use ControllerTraits\PublicationControllerTrait;

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
        $view = $this->getView();
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

            $isGrantable = $this->getServiceLocator()->get('BusinessServiceManager')
                ->get('Bus\BusReg')
                ->isGrantable($busReg['id']);

            $view->setVariable('noDecisionStatuses', $newVariationCancellation);
            $view->setVariable('busReg', $busReg);
            $view->setVariable('isGrantable', $isGrantable);
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
            'version' => $busReg['version'],
            'statusChangeDate' => $this->getStatusChangeDate()
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
        $busRegId = $this->params()->fromRoute('busRegId');
        $isGrantable = $this->getServiceLocator()->get('BusinessServiceManager')
            ->get('Bus\BusReg')
            ->isGrantable($busRegId);

        if (!$isGrantable) {
            return false; //shouldn't happen as button will be hidden!
        } else {
            $view = $this->getView();

            $busReg = $this->getBusReg();
            $service = $this->getServiceLocator()->get('DataServiceManager')->get('Common\Service\Data\BusReg');

            switch ($busReg['status']['id']) {
                case 'breg_s_new':
                    $data = [
                        'id' => $busReg['id'],
                        'status' => 'breg_s_registered',
                        'revertStatus' => $busReg['status']['id'],
                        'version' => $busReg['version'],
                        'statusChangeDate' => $this->getStatusChangeDate()
                    ];

                    $service->save($data);

                    $this->publishBusReg();

                    return $this->redirectToIndex();
                case 'breg_s_cancellation':
                    $data = [
                        'id' => $busReg['id'],
                        'status' => 'breg_s_cancelled',
                        'revertStatus' => $busReg['status']['id'],
                        'version' => $busReg['version'],
                        'statusChangeDate' => $this->getStatusChangeDate()
                    ];

                    $service->save($data);

                    $this->publishBusReg();

                    return $this->redirectToIndex();
                case 'breg_s_var':
                    $form = $this->generateFormWithData(
                        'BusRegVariationReason',
                        'processGrantVariation',
                        $this->getDataForForm()
                    );

                    if ($this->getIsSaved()) {
                        $this->publishBusReg();

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
     * Action to republish a bus registration
     *
     * @return mixed
     */
    public function republishAction()
    {
        if ($this->publishBusReg()) {
            $this->addSuccessMessage('Publication was republished successfully');
        } else {
            $this->addErrorMessage('Sorry; there was a problem. Please try again.');
        }

        return $this->redirectToIndex();
    }

    /**
     * Publish Bus Reg
     *
     * @return bool
     */
    private function publishBusReg()
    {
        $filter = $this->getPublishBusRegFilter();

        if (empty($filter)) {
            return false;
        }

        $busReg = $this->getBusReg();

        $publishData = [
            'busReg' => $busReg['id'],
            'licence' => $busReg['licence']['id'],
            'previousStatus' => $busReg['revertStatus']['id']
        ];

        if (empty($busReg['trafficAreas'])) {
            return false;
        }

        $trafficAreasToPublish = array_column($busReg['trafficAreas'], 'id');

        $this->getPublicationHelper()->publishMultiple(
            $publishData,
            $trafficAreasToPublish,
            'N&P',
            $filter
        );

        return true;
    }

    /**
     * Get Publish Bus Reg Filter for status
     *
     * @return string
     */
    private function getPublishBusRegFilter()
    {
        $busReg = $this->getBusReg();

        $status = $busReg['revertStatus']['id'];

        $statusToFilterMapper = [
            BusRegistration::STATUS_NEW => 'BusRegGrantNewPublicationFilter',
            BusRegistration::STATUS_CANCEL => 'BusRegGrantCancelPublicationFilter',
            BusRegistration::STATUS_VAR => 'BusRegGrantVarPublicationFilter',
        ];

        return !empty($statusToFilterMapper[$status]) ? $statusToFilterMapper[$status] : null;
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
        $data['fields']['statusChangeDate'] = $this->getStatusChangeDate();

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
                $data['fields']['statusChangeDate'] = $this->getStatusChangeDate();
                break;
            case 'breg_s_refused':
                $data['fields']['reasonRefused'] = $data['fields']['reason'];
                $data['fields']['statusChangeDate'] = $this->getStatusChangeDate();
                break;
            case 'breg_s_withdrawn':
                $data['fields']['withdrawnReason'] = $data['fields']['reason'];
                $data['fields']['statusChangeDate'] = $this->getStatusChangeDate();
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

    private function getStatusChangeDate()
    {
        return date("Y-m-d H:i:s");
    }

    /**
     * Edit action is actually the index action. Action is being picked up as fees on other bus reg pages. This fixes
     * navigation. This fixes 'Processing' navigation. Should have a more thorough solution.
     * @to-do Fix Bus Reg navigation default action being picked up.
     * @return \Zend\Stdlib\ResponseInterface|\Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        return $this->indexAction();
    }

    /**
     * Fees action is actually the index action. Action is being picked up as fees on fees page. This fixes
     * 'Processing' navigation.  Should have a more thorough solution.
     * @to-do Fix Bus Reg navigation default action being picked up.
     * @return \Zend\Stdlib\ResponseInterface|\Zend\View\Model\ViewModel
     */
    public function feesAction()
    {
        return $this->indexAction();
    }

    /**
     * Documents action is actually the index action. Default action is edit, so action is being picked up for all
     * navigation. This fixes 'Processing' navigation. Should have a more thorough solution.
     * @to-do Fix Bus Reg navigation default action being picked up.
     * @return \Zend\Stdlib\ResponseInterface|\Zend\View\Model\ViewModel
     */
    public function documentsAction()
    {
        return $this->indexAction();
    }
}
