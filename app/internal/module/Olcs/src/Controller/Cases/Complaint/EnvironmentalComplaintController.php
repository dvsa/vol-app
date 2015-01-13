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
                'fields',
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
            'complainantContactDetails' => [
                'children' => [
                    'person' => [
                        'forename',
                        'familyName'
                    ]
                ]
            ]
        )
    );

    public function processLoad($data)
    {
        if (isset($data['complainantContactDetails']['person'])) {
            $data['complainantForename'] = $data['complainantContactDetails']['person']['forename'];
            $data['complainantFamilyName'] = $data['complainantContactDetails']['person']['familyName'];
        }

        return parent::processLoad($data);
    }

    public function processSave($data)
    {
        $data['fields']['isCompliance'] = 0;

        $addressService = $this->getServiceLocator()
            ->get('DataServiceManager')
            ->get('Generic\Service\Data\Address');

        $personId = $this->savePerson($data);

        $contactDetailsService = $this->getServiceLocator()
            ->get('DataServiceManager')
            ->get('Generic\Service\Data\ContactDetails');

        $addressSaved = $this->getServiceLocator()->get('Entity\Address')->save($data['fields']['address']);
        $addressId = isset($addressSaved['id']) ? $addressSaved['id'] : $data['address']['id'];

        $contactDetails = [
            'person' => $personId,
            'address' => $addressId,
            'contactType' => 'ct_complainant'
        ];

        $contactDetailsId = $contactDetailsService->save($contactDetails);

        $data['fields']['complainantContactDetails'] = $contactDetailsId;

        $data = $this->determineCloseDate($data);

        $result = parent::processSave($data, false);

        // save related operating centres to ocComplaint table
        $complaintId = isset($result['id']) ? $result['id'] : $data['fields']['id'];
        $data = $this->saveAffectedCentres($complaintId, $data);

        return $this->redirectToIndex();
    }

    private function savePerson($data)
    {
        $personService = $this->getServiceLocator()
            ->get('DataServiceManager')
            ->get('Generic\Service\Data\Person');

        if (isset($data['fields']['id']) && !empty($data['fields']['id'])) {
            //prevent the person id from ever being overwritten
            if (isset($data['fields']['complainantContactDetails'])) {
                unset($data['fields']['complainantContactDetails']);
            }

            //get the current person id
            $existing = $this->loadCurrent();

            //we may not need to modify the person details at all
            $person = $existing['complainantContactDetails']['person'];

            if ($data['fields']['complainantForename'] != $person['forename']
                || $data['fields']['complainantFamilyName'] != $person['familyName']) {
                $person['forename'] = $data['fields']['complainantForename'];
                $person['familyName'] = $data['fields']['complainantFamilyName'];

                return $personService->save($person);
            }
        } else {
            $person['forename'] = $data['fields']['complainantForename'];
            $person['familyName'] = $data['fields']['complainantFamilyName'];
            return $personService->save($person);
        }
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

    private function saveAffectedCentres($complaintId, $data)
    {
        // clear any existing
        $this->makeRestCall('OcComplaint', 'DELETE', ['complaint' => $complaintId]);

        if (is_array($data['fields']['affectedCentres'])) {
            foreach ($data['fields']['affectedCentres'] as $operatingCentreId) {
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

/*
    public function processLoad($data)
    {
        $service = $this->getServiceLocator()->get('DataServiceManager')->get('Olcs\Service\Data\Mapper\Opposition');

        $data = $service->formatLoad($data);

        $data = parent::processLoad($data);

        return $data;
    }

    /**
     * Gets the case by ID.
     *
     * @param integer $id
     * @return array
     */
    public function getCase($id)
    {
        $service = $this->getServiceLocator()->get('DataServiceManager')->get('Olcs\Service\Data\Cases');
        return $service->fetchCaseData($id);
    }
}
