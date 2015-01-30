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
                'reason' => $reason
            ];

            $view->setVariable('decisionData', $data);
        } else if (in_array($busReg['status']['id'], $newVariationCancellation)
            || $busReg['status']['id'] == 'breg_s_registered') {
            $view->setVariable('noDecisionStatuses', $newVariationCancellation);
            $view->setVariable('busReg', $busReg);
        }

        $view->setTemplate('pages/bus/processing-decision');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        return $this->renderView($view);
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
                $form = 'BusRegStatusWithdrawn';
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
     * Process a status update - there are 7 or 8 more stories around this so we'll eventually use a
     * service for all of them
     *
     * @param $data
     * @return mixed
     */
    public function processUpdateStatus($data)
    {
        $data['lastModifiedBy'] = $this->getLoggedInUser();

        switch ($data['fields']['status']) {
            case 'breg_s_admin':
                $data['fields']['reasonCancelled'] = $data['fields']['reason'];
                break;
        }

        parent::processSave($data, false);

        return $this->redirectToIndex();
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
