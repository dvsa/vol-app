<?php

/**
 * Inspection Request Trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Traits;

use Zend\View\Model\ViewModel;
use Common\Service\Entity\InspectionRequestEntityService;

/**
 * Inspection Request Trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
trait InspectionRequestTrait
{
    /**
     * Index action
     */
    public function indexAction()
    {
        $this->checkForCrudAction(null, [], 'id');

        $table = $this->getInspectionRequestTable();

        $this->loadScripts(['table-actions']);

        $view = new ViewModel(['table' => $table]);

        $view->setTemplate('partials/table');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        return $this->renderView($view);
    }

    /**
     * Get inspection request table
     *
     * @return Common\Service\Table\TableBuilder
     */
    protected function getInspectionRequestTable()
    {
        $params = [
            'page'    => $this->getQueryOrRouteParam('page', 1),
            'sort'    => $this->getQueryOrRouteParam('sort', 'id'),
            'order'   => $this->getQueryOrRouteParam('order', 'desc'),
            'limit'   => $this->getQueryOrRouteParam('limit', 10),
            'query'   => $this->getRequest()->getQuery(),
        ];
        $results = $this->getServiceLocator()
            ->get('Entity\InspectionRequest')
            ->getInspectionRequestList($params, $this->getCurrentLicence());
        return $this->getTable('inspectionRequest', $results, $params);
    }

    /**
     * Edit action
     *
     */
    public function editAction()
    {
        return $this->formAction('edit');
    }

    /**
     * Add action
     *
     */
    public function addAction()
    {
        return $this->formAction('add');
    }

    /**
     * Form action
     *
     * @param string $type
     * @return mixed
     */
    public function formAction($type)
    {
        $this->setUpOcListbox();
        $form = $this->getForm('InspectionRequest');
        $request = $this->getRequest();

        $enforcementAreaName = $this->getEnforcementAreaName();

        if ($type == 'add') {
            if (!$enforcementAreaName) {
                $this->addErrorMessage('internal-inspection-request.area-not-set');
                return $this->redirectToIndex();
            }
            $form = $this->setDefaultFormValues($form);
        } else {
            $form = $this->populateForm($form);
        }

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $result = $this->getServiceLocator()->get('BusinessServiceManager')
                    ->get('InspectionRequest')
                    ->process(
                        [
                            'data' => $form->getData()['data'],
                            'licenceId' => $this->getCurrentLicence(),
                            'applicationId' => $this->fromRoute('application'),
                            'type' => $this->type
                        ]
                    );

                if ($result->isOk()) {
                    $message = ($type == 'edit') ?
                        'internal-inspection-request-inspection-request-updated' :
                        'internal-inspection-request-inspection-request-added';
                    $this->addSuccessMessage($message);
                } else {
                    $message = 'internal-inspection-request-inspection-request-failed';
                    $this->addErrorMessage($message);
                }

                return $this->redirectToIndex();
            }
        }
        $view = new ViewModel(
            [
                'form' => $form,
                'inspectionId' => ($type == 'edit') ? $this->fromRoute('id') : '',
                'area' => $enforcementAreaName
            ]
        );

        $view->setTemplate('partials/form-inspection-request');
        $translator = $this->getServiceLocator()->get('translator');
        return $this->renderView(
            $view,
            ($type == 'edit') ?
            $translator->translate('internal-application-processing-inspection-request-edit') :
            $translator->translate('internal-application-processing-inspection-request-add')
        );
    }

    /**
     * Get enforcement area name
     *
     * @return string
     */
    protected function getEnforcementAreaName()
    {
        $licence = $this->getServiceLocator()->get('Entity\Licence')->getEnforcementArea($this->getCurrentLicence());
        return isset($licence['enforcementArea']['name']) ? $licence['enforcementArea']['name'] : '';
    }

    /**
     * Set default form values
     *
     * @param $form Common\Form\Form
     * @return Common\Form\Form
     */
    protected function setDefaultFormValues($form)
    {
        $form->get('data')
            ->get('reportType')
            ->setValue(InspectionRequestEntityService::REPORT_TYPE_MAINTENANCE_REQUEST);

        $today = $this->getServiceLocator()->get('Helper\Date')->getDate();
        $form->get('data')
            ->get('requestDate')
            ->setValue($today);

        $form->get('data')
            ->get('resultType')
            ->setValue(InspectionRequestEntityService::RESULT_TYPE_NEW);

        return $form;
    }

    /**
     * Populate form
     *
     * @param $form Common\Form\Form
     * @return Common\Form\Form
     */
    protected function populateForm($form)
    {
        $inspectionRequestId = $this->fromRoute('id');
        $inspectionRequest = $this->getServiceLocator()
            ->get('Entity\InspectionRequest')
            ->getInspectionRequest($inspectionRequestId);

        $data = [
            'data' => [
                'id' => $inspectionRequest['id'],
                'version' => $inspectionRequest['version'],
                'reportType' => $inspectionRequest['reportType']['id'],
                'operatingCentre' => $inspectionRequest['operatingCentre']['id'],
                'inspectorName' => $inspectionRequest['inspectorName'],
                'requestType' => $inspectionRequest['requestType']['id'],
                'requestDate' => $inspectionRequest['requestDate'],
                'dueDate' => $inspectionRequest['dueDate'],
                'returnDate' => $inspectionRequest['returnDate'],
                'resultType' => $inspectionRequest['resultType']['id'],
                'fromDate' => $inspectionRequest['fromDate'],
                'toDate' => $inspectionRequest['toDate'],
                'vehiclesExaminedNo' => $inspectionRequest['vehiclesExaminedNo'],
                'trailersExaminedNo' => $inspectionRequest['trailersExaminedNo'],
                'requestorNotes' => $inspectionRequest['requestorNotes'],
                'inspectorNotes' => $inspectionRequest['inspectorNotes']
            ]
        ];
        $form->setData($data);
        return $form;
    }
}
