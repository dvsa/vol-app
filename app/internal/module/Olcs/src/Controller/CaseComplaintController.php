<?php

/**
 * Case Complaint Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace Olcs\Controller;

use Common\Controller\CrudInterface;
use Olcs\Controller\Traits\DeleteActionTrait;
use Zend\View\Model\ViewModel;

/**
 * Case Complaint Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */
class CaseComplaintController extends CaseController implements CrudInterface
{
    use DeleteActionTrait;

    /**
     * Should return the name of the service to call for deleting the item
     *
     * @return string
     */
    public function getDeleteServiceName()
    {
        return 'Complaint';
    }

    /**
     * Main index action responsible for generating the main landing page for
     * complaints.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');
        $licenceId = $this->fromRoute('licence');

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licenceId)));

        // checks for CRUD and redirects as required
        $this->checkForCrudAction('complaint', array('case' => $caseId, 'licence' => $licenceId), 'id');

        // no crud, generate the main complaints table
        $view = $this->getView();
        $tabs = $this->getTabInformationArray();
        $action = 'complaints';

        $case = $this->getCase($caseId);

        $summary = $this->getCaseSummaryArray($case);

        $bundle = $this->getComplaintBundle();

        $results = $this->makeRestCall(
            'Cases', 'GET', array(
            'id' => $caseId, 'bundle' => json_encode($bundle))
        );

        $data = [];
        $data['url'] = $this->getPluginManager()->get('url');

        $table = $this->buildTable('complaints', $results['complaints'], $data);

        $view->setVariables(
            [
            'case' => $case,
            'tabs' => $tabs,
            'tab' => $action,
            'summary' => $summary,
            'table' => $table,
            ]
        );

        $view->setTemplate('case/manage');
        return $view;
    }

    /**
     * Add form action
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $routeParams = $this->getParams(array('case', 'licence', 'id'));

        if (null !== $this->params()->fromPost('cancel-complaint')) {
            return $this->redirect()->toRoute(
                'case_complaints', array(
                    'licence' => $routeParams['licence'],
                    'case' => $routeParams['case']
                )
            );
        }
        // Below is for setting route params for the breadcrumb
        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $routeParams['licence']),
                'case_complaints' => array(
                    'licence' => $routeParams['licence'],
                    'case' => $routeParams['case']
                )
            )
        );

        $data = array('case' => $routeParams['case']);

        // todo hardcoded organisation id for now
        $results = $this->makeRestCall('Cases', 'GET', array('id' => $routeParams['case']));

        // todo hardcoded organisation id for now
        $data['organisation-details']['id'] = 7;
        $data['organisation-details']['version'] = 1;

        if (empty($routeParams['case']) || empty($routeParams['licence']) || empty($results)) {
            return $this->getResponse()->setStatusCode(404);
        }

        $form = $this->generateForm(
            'complaint', 'processComplaint'
        );

        $form->setData($data);
        //$form->setMessages(array('blah' => 'This is a test message'));
        $view = new ViewModel(
            array(
                'form' => $form,
                'params' => array(
                    'pageTitle' => 'add-complaint',
                    'pageSubTitle' => 'subtitle-complaint-text'
                )
            )
        );
        $view->setTemplate('complaint/form');
        return $view;
    }

    public function editAction()
    {
        $routeParams = $this->getParams(
            array(
                'case',
                'licence',
                'id',
            )
        );
        if (null !== $this->params()->fromPost('cancel-complaint')) {
            return $this->redirect()->toRoute(
                'case_complaints', array(
                    'licence' => $routeParams['licence'],
                    'case' => $routeParams['case']
                )
            );
        }
        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $routeParams['licence']),
                'case_complaints' => array('licence' => $routeParams['licence'], 'case' => $routeParams['case'])
            )
        );

        $bundle = $this->getComplaintBundleForUpdates();

        $data = $this->makeRestCall(
            'Complaint',
            'GET',
            array('id' => $routeParams['id'], 'bundle' => json_encode($bundle))
        );

        if (isset($data['id'])) {
            $data['case'] = $data['id'];
        }

        if (empty($routeParams['case']) || empty($routeParams['licence']) || empty($data)) {
            return $this->getResponse()->setStatusCode(404);
        }

        $data['organisation-details'] = $data['organisation'];
        $data['complaint-details'] = $data;
        $data['complainant-details'] = $data['complainant']['person'];
        $data['driver-details'] = $data['driver']['contactDetails']['person'];

        $form = $this->generateFormWithData(
            'complaint', 'processComplaint', $data
        );

        $view = new ViewModel(
            array(
                'form' => $form,
                'params' => array(
                    'pageTitle' => 'edit-complaint',
                    'pageSubTitle' => 'subtitle-complaint-text'
                )
            )
        );
        $view->setTemplate('complaint/form');
        return $view;
    }

    public function processComplaint($data)
    {
        $routeParams = $this->getParams(array('action', 'licence', 'case'));

        if (strtolower($routeParams['action']) == 'edit') {
            // not sure how the version info is to be handled for entities
            // that are not directly updated (e.g. ContactDetails)
            // todo this *may* be possible in a single rest call
            $result = $this->processEdit($data['complaint-details'], 'Complaint');
            $result = $this->processEdit($data['complainant-details'], 'Person');
            $result = $this->processEdit($data['driver-details'], 'Person');
        } else {
            // configure complaint data
            unset($data['complaint-details']['version']);
            unset($data['organisation-details']['version']);

            $newData = $data['complaint-details'];
            $newData['Cases'][] = $data['case'];
            $newData['value'] = '';
            $newData['vehicle_id'] = 1;
            $newData['organisation'] = 1;

            $newData['driver']['contactDetails']['contactDetailsType'] = 'Driver';
            $newData['driver']['contactDetails']['is_deleted'] = 0;
            $newData['driver']['contactDetails']['person'] = $data['driver-details'];
            unset($newData['driver']['contactDetails']['person']['version']);

            $newData['complainant']['contactDetailsType'] = 'Complainant';
            $newData['complainant']['is_deleted'] = 0;
            $newData['complainant']['person'] = $data['complainant-details'];
            unset($newData['complainant']['person']['version']);

            $result = $this->processAdd($newData, 'Complaint');

        }

        return $this->redirect()->toRoute(
            'case_complaints',
            array(
                'case' => $routeParams['case'], 'licence' => $routeParams['licence']
            )
        );
    }

    /**
     * Method to return the bundle required for getting complaints and related
     * entities from the database.
     *
     * @return array
     */
    private function getComplaintBundleForUpdates()
    {
        return array(
            'complaint' => array(
                'properties' => array('ALL'),
            ),
            'children' => array(
                'driver' => array(
                    'properties' => array('id', 'version'),
                    'children' => array(
                        'contactDetails' => array(
                            'properties' => array('id', 'version'),
                            'children' => array(
                                'person' => array(
                                    'properties' => array(
                                        'id',
                                        'version',
                                        'firstName',
                                        'middleName',
                                        'surname',
                                    )
                                )
                            )
                        )
                    )
                ),
                'complainant' => array(
                    'properties' => array('person'),
                    'children' => array(
                        'person' => array(
                            'properties' => array(
                                'id',
                                'version',
                                'firstName',
                                'middleName',
                                'surname',
                            )
                        )
                    )
                ),
                'organisation' => array(
                    'properties' => array('id', 'version', 'name'),
                )
            )
        );
    }

    /**
     * Method to return the bundle required for complaints
     *
     * @return array
     */
    private function getComplaintBundle()
    {
        return array(
            'properties' => array(
                'id'
            ),
            'children' => array(
                'complaints' => array(
                    'properties' => array(
                        'id',
                        'complaintDate',
                        'description',
                        'complainant'
                    ),
                    'children' => array(
                        'complainant' => array(
                            'properties' => array(
                                'id',
                                'person'
                            ),
                           'children' => array(
                               'person' => array(
                                   'properties' => array(
                                       'firstName',
                                       'middleName',
                                       'surname',
                                   )
                               )
                           )
                        )
                    )
                )
            )
        );
    }
}
