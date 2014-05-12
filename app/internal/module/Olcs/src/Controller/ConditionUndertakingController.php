<?php

/**
 * ConditionUndertaking controller
 *
 * Adds/edits a conditionUndertaking
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace Olcs\Controller;

use Common\Controller\FormActionController;
use Zend\View\Model\ViewModel;

/**
 * ConditionUndertaking controller
 *
 * Adds/edits a conditionUndertaking
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */
class ConditionUndertakingController extends FormActionController
{

    /**
     * Add form action
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $routeParams = $this->getParams(array('case', 'licence', 'id', 'type'));
        $type = $routeParams['type'];

        if (null !== $this->params()->fromPost('cancel-conditionUndertaking')) {
            return $this->redirect()->toRoute(
                'case_conditions_undertakings', array(
                    'licence' => $routeParams['licence'],
                    'case' => $routeParams['case']
                )
            );
        }
        // Below is for setting route params for the breadcrumb
        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $routeParams['licence']),
                'case_conditions_undertakings' => array('case' => $routeParams['case'])
            )
        );

        $data = array('conditionType' => $type, 'vosaCase' => $routeParams['case']);

        // todo hardcoded organisation id for now
        $results = $this->makeRestCall('VosaCase', 'GET', array('id' => $routeParams['case']));

        // todo hardcoded organisation id for now
        $data['organisation-details']['id'] = 7;
        $data['organisation-details']['version'] = 1;

        if (empty($routeParams['case']) || empty($routeParams['licence']) || empty($results)) {
            return $this->getResponse()->setStatusCode(404);
        }

        $form = $this->generateForm(
            'condition-undertaking-form', 'processConditionUndertaking'
        );
        
        // set form dependent aspects
        $form->get('condition-undertaking')->get('description')->setLabel(ucfirst($type));

        $form->setData($data);
        //$form->setMessages(array('blah' => 'This is a test message'));
        $view = new ViewModel(
            array(
            'form' => $form,
            'headScript' => array('/static/js/conditionUndertaking.js'),
            'params' => array(
                'pageTitle' => 'add-' . $type,
                'pageSubTitle' => 'subtitle-' . $type . '-text'
            )
            )
        );
        $view->setTemplate('conditionUndertaking/form');
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
        if (null !== $this->params()->fromPost('cancel-conditionUndertaking')) {
            return $this->redirect()->toRoute(
                'case_conditionUndertakings', array(
                    'licence' => $routeParams['licence'],
                    'case' => $routeParams['case']
                )
            );
        }
        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $routeParams['licence']),
                'case_conditionUndertakings' => array('licence' => $routeParams['licence'], 'case' => $routeParams['case'])
            )
        );

        $bundle = $this->getConditionUndertakingBundle();

        $data = $this->makeRestCall('ConditionUndertaking', 'GET', array('id' => $routeParams['id'], 'bundle' => json_encode($bundle)));
        if (isset($data['id'])) {
            $data['vosaCase'] = $data['id'];
        }

        if (empty($routeParams['case']) || empty($routeParams['licence']) || empty($data)) {
            return $this->getResponse()->setStatusCode(404);
        }

        $data['organisation-details'] = $data['organisation'];
        $data['conditionUndertaking-details'] = $data;
        $data['complainant-details'] = $data['complainant']['person'];
        $data['driver-details'] = $data['driver']['contactDetails']['person'];

        $form = $this->generateFormWithData(
            'conditionUndertaking', 'processConditionUndertaking', $data, true
        );

        $view = new ViewModel(
            array(
                'form' => $form,
                'headScript' => array(
                    '/static/js/conditionUndertaking.js'
                ),
                'params' => array(
                    'pageTitle' => 'edit-conditionUndertaking',
                    'pageSubTitle' => 'subtitle-conditionUndertaking-text'
                )
            )
        );
        $view->setTemplate('conditionUndertaking/form');
        return $view;
    }

    public function processConditionUndertaking($data)
    {
        $routeParams = $this->getParams(array('action', 'licence', 'case'));

        if (strtolower($routeParams['action']) == 'edit') {
            // not sure how the version info is to be handled for entities
            // that are not directly updated (e.g. ContactDetails)
            // todo this *may* be possible in a single rest call
            $result = $this->processEdit($data['conditionUndertaking-details'], 'ConditionUndertaking');
            $result = $this->processEdit($data['complainant-details'], 'Person');
            $result = $this->processEdit($data['driver-details'], 'Person');
        } else {
            // configure conditionUndertaking data
            unset($data['conditionUndertaking-details']['version']);
            unset($data['organisation-details']['version']);

            $newData = $data['conditionUndertaking-details'];
            $newData['vosaCases'][] = $data['vosaCase'];
            $newData['value'] = '';
            $newData['vehicle_id'] = 1;
            $newData['organisation'] = $data['organisation-details'];

            $newData['driver']['contactDetails']['contactDetailsType'] = 'Driver';
            $newData['driver']['contactDetails']['is_deleted'] = 0;
            $newData['driver']['contactDetails']['person'] = $data['driver-details'];
            unset($newData['driver']['contactDetails']['person']['version']);

            $newData['complainant']['contactDetailsType'] = 'Complainant';
            $newData['complainant']['is_deleted'] = 0;
            $newData['complainant']['person'] = $data['complainant-details'];
            unset($newData['complainant']['person']['version']);

            $result = $this->processAdd($newData, 'ConditionUndertaking');

        }

        return $this->redirect()->toRoute(
            'case_conditionUndertakings',
            array(
                'case' => $routeParams['case'], 'licence' => $routeParams['licence']
            )
        );
    }

    /**
     * Method to return the bundle required for getting conditionUndertakings and related
     * entities from the database.
     *
     * @return array
     */
    private function getConditionUndertakingBundle()
    {
        return array(
           'conditionUndertaking' => array(
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
}
