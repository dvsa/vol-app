<?php

/**
 * Case EnvironmentalComplaint Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace Olcs\Controller\Cases\Complaint;

// Olcs
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

use Zend\View\Model\ViewModel;

/**
 * Case EnvironmentalComplaint Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */
class EnvironmentalComplaintController extends OlcsController\CrudAbstract
    implements OlcsController\Interfaces\CaseControllerInterface
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'complaint';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'environmental-complaint';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case-section';

    /**
     * For most case crud controllers, we use the layout/case-details-subsection
     * layout file. Except submissions.
     *
     * @var string
     */
    protected $pageLayoutInner = 'layout/case-details-subsection';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Complaint';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_details_complaints';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = [
        'case',
        'isCompliance'
    ];

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
     * Holds the isAction
     *
     * @var boolean
     */
    protected $isAction = false;

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'children' => array(
            'case' => [],
            'complaintType' => [],
            'status' => [],
            'ocComplaints' => array(
                'children' => array(
                    'operatingCentre'
                )
            ),
            'complainantContactDetails' => [
                'children' => [
                    'address' => array(
                        'children' => array(
                            'countryCode' => array(
                                'properties' => array(
                                    'id'
                                )
                            )
                        )
                    ),
                    'person' => [
                        'forename',
                        'familyName'
                    ]
                ]
            ]
        )
    );

    /**
     * Formats data into format required for form.
     *
     * @param array $data
     * @return array
     */
    public function processLoad($data)
    {
        if (isset($data['complainantContactDetails']['address'])) {
            $data['address'] = $data['complainantContactDetails']['address'];
        }

        if (isset($data['complainantContactDetails']['person'])) {
            $data['complainantForename'] = $data['complainantContactDetails']['person']['forename'];
            $data['complainantFamilyName'] = $data['complainantContactDetails']['person']['familyName'];
        }
        $data['fields']['isCompliance'] = 0;

        if (isset($data['closeDate'])) {
            $data['status'] = 'cst_closed';
        } else {
            $data['status'] = 'cst_open';
        }

        $ocComplaints = [];

        if (isset($data['ocComplaints'])) {
            foreach ($data['ocComplaints'] as $ocComplaint)
            {
                $ocComplaints[] = $ocComplaint['operatingCentre']['id'];
            }
        }
        $data['ocComplaints'] = $ocComplaints;

        $data = parent::processLoad($data);

        return $data;
    }

    /**
     * Method to save the form data, called when inserting or editing.
     *
     * @param array $data
     * @return array|mixed|\Zend\Http\Response
     */
    public function processSave($data)
    {
        $data['fields']['isCompliance'] = 0;

        $contactDetailsId = $this->saveComplainant($data);

        $data['fields']['complainantContactDetails'] = $contactDetailsId;

        $data = $this->determineCloseDate($data);

        $result = parent::processSave($data, false);

        // save related operating centres to ocComplaint table
        $complaintId = isset($result['id']) ? $result['id'] : $data['fields']['id'];
        $data = $this->saveOcComplaints($complaintId, $data);

        return $this->redirectToIndex();
    }

    /**
     * Saves the person entity, if required based on data.
     * Prevent the person id from ever being overwritten by inserting a new record if the complainant name changes
     * or keep existing if unchanged.
     * @param $data
     * @return mixed
     */
    private function saveComplainant($data)
    {
        $personService = $this->getServiceLocator()
            ->get('DataServiceManager')
            ->get('Generic\Service\Data\Person');

        $contactDetailsService = $this->getServiceLocator()
            ->get('DataServiceManager')
            ->get('Generic\Service\Data\ContactDetails');

        if (isset($data['fields']['id']) && !empty($data['fields']['id'])) {
            //get the current person id
            $existing = $this->loadCurrent();

            //we may not need to modify the person details at all
            $person = $existing['complainantContactDetails']['person'];

            $contactDetailsToSave = ['id' => $existing['complainantContactDetails']['id']];

            if ($data['fields']['complainantForename'] != $person['forename']
                || $data['fields']['complainantFamilyName'] != $person['familyName']) {
                $person['forename'] = $data['fields']['complainantForename'];
                $person['familyName'] = $data['fields']['complainantFamilyName'];

                $personId = $personService->save($person);

                $contactDetailsToSave = [
                    'id' => $existing['complainantContactDetails']['id'],
                    'version' => $data['fields']['complainantContactDetails']['version'],
                    'person' => $personId
                ];
            }
        } else {
            $person['forename'] = $data['fields']['complainantForename'];
            $person['familyName'] = $data['fields']['complainantFamilyName'];
            $personId = $personService->save($person);

            $addressSaved = $this->getServiceLocator()->get('Entity\Address')->save($data['fields']['address']);
            $addressId = isset($addressSaved['id']) ? $addressSaved['id'] : $data['address']['id'];

            $contactDetailsToSave = [
                'person' => $personId,
                'address' => $addressId,
                'contactType' => 'ct_complainant'
            ];
        }

        if (!empty($contactDetailsToSave)) {
            $result = $contactDetailsService->save($contactDetailsToSave);
        }

        return isset($result) ? $result : $contactDetailsToSave['id'];
    }

    /**
     * Convert a open/closed status to a closeDate.
     *
     * @param array $data
     * @return array $data
     */
    private function determineCloseDate($data)
    {
        if ($data['fields']['status'] == 'cst_closed') {
            $data['fields']['closeDate'] = time();
        } else {
            $data['fields']['closeDate'] = null;
        }
        unset($data['fields']['status']);
        return $data;
    }

    private function saveOcComplaints($complaintId, $data)
    {
        // clear any existing
        $this->makeRestCall('OcComplaint', 'DELETE', ['complaint' => $complaintId]);

        if (is_array($data['fields']['ocComplaints'])) {
            foreach ($data['fields']['ocComplaints'] as $operatingCentreId) {
                $ocoParams = array('complaint' => $complaintId);
                $ocoParams['operatingCentre'] = $operatingCentreId;
                $this->makeRestCall('OcComplaint', 'POST', $ocoParams);
            }
        }

        return $data;
    }

    /**
     * Redirect to oppositions page which shows list of env complaints.
     */
    public function redirectToIndex()
    {
        return $this->redirectToRoute(
            'case_opposition',
            ['action'=>'index', 'case' => $this->params()->fromRoute('case')],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            false
        );
    }
}
