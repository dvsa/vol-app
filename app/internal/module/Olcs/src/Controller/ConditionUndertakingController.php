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
     * Method to generate the add form
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $routeParams = $this->getParams(array('case', 'licence', 'type'));
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

        if (empty($routeParams['case']) || empty($routeParams['licence']) || empty($results)) {
            return $this->getResponse()->setStatusCode(404);
        }

        $form = $this->generateForm(
            'condition-undertaking-form', 'processConditionUndertaking'
        );

        // set form dependent aspects
        $form->get('condition-undertaking')->get('notes')->setLabel(ucfirst($type));

        $form->setData($data);
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

    /**
     * Method to generate the edit form
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $routeParams = $this->getParams(array('case', 'licence', 'type', 'id'));
        $type = $routeParams['type'];

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
                'case_conditions_undertakings' => array('case' => $routeParams['case'])
            )
        );

        $bundle = $this->getConditionUndertakingBundle();

        $data['condition-undertaking'] = $this->makeRestCall('ConditionUndertaking', 'GET', array('id' => $routeParams['id'], 'bundle' => json_encode($bundle)));

        if (empty($routeParams['case']) || empty($routeParams['licence']) || empty($data)) {
            return $this->getResponse()->setStatusCode(404);
        }

        // assign data as required by the form
        $data['condition-undertaking']['caseId'] = $data['condition-undertaking']['vosaCase']['id'];

        $form = $this->generateFormWithData(
            'condition-undertaking-form', 'processConditionUndertaking', $data, true
        );

        $ocAddressList = $this->getOCAddressByLicence($routeParams['licence']);

        // set form dependent aspects
        $form->get('condition-undertaking')->get('notes')->setLabel(ucfirst($type));
        $form->get('condition-undertaking')->get('operatingCentreAddressId')->setValueOptions($ocAddressList);


        $view = new ViewModel(
            array(
                'form' => $form,
                'headScript' => array(
                    '/static/js/conditionUndertaking.js'
                ),
                'params' => array(
                    'pageTitle' => 'edit-' . $type,
                    'pageSubTitle' => 'subtitle-' . $type . '-text'
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
     * Method to extract all Operating Centre Addresses for a given licence
     * and reformat into array suitable for select options
     *
     * @param integer $licenceId
     * @return array address_id => [address details]
     */
    private function getOCAddressByLicence($licenceId)
    {
        $operatingCentreAddressBundle = $this->getOCAddressBundle();
        $result = $this->makeRestCall(
                'OperatingCentre',
                'GET',
                array(
                    'licence' => $licenceId,
                    'bundle' => json_encode($operatingCentreAddressBundle)
                )
        );

        $operatingCentreAddresses = array();

        if ($result['Count'])
        {
            foreach($result['Results'] as $oc)
            {
                $address = $oc['address'];
                $operatingCentreAddresses[$oc['id']] =
                    $address['addressLine1'] . ', ' .
                    $address['addressLine2'] . ', ' .
                    $address['addressLine3'] . ', ' .
                    $address['addressLine4'] . ', ' .
                    $address['postcode'] . ', ' .
                    $address['country'];
            }
        }
        return $operatingCentreAddresses;
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
            'children' => array(
                'vosaCase' => array(
                    'properties' => array(
                        'id',
                    ),
                ),
                'operatingCentre' => array(
                    'properties' => array(
                        'id',
                        'address',
                    ),
                    'children' => array(
                        'address' => array(
                            'properties' => array(
                                'addressLine1',
                                'addressLine2',
                                'addressLine3',
                                'addressLine4',
                                'paon_desc',
                                'saon_desc',
                                'street',
                                'locality',
                                'postcode',
                                'country'
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * Method to return the bundle required for getting all operating centre
     * addresses for a given licence
     *
     * @return array
     */
    private function getOCAddressBundle()
    {
        return array(
            'properties' => array(
                'id',
                'address'
            ),
            'children' => array(
                'address' => array(
                    'properties' => array(
                        'id',
                        'addressLine1',
                        'addressLine2',
                        'addressLine3',
                        'addressLine4',
                        'paon_desc',
                        'saon_desc',
                        'street',
                        'locality',
                        'postcode',
                        'country'
                    )
                )
            )
        );
    }
}
