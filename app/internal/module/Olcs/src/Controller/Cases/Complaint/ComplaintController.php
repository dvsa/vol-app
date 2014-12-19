<?php

/**
 * Case Complaint Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace Olcs\Controller\Cases\Complaint;

// Olcs
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

use Zend\View\Model\ViewModel;

/**
 * Case Complaint Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */
class ComplaintController extends OlcsController\CrudAbstract
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
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'complaint';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'complaint';

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
        'properties' => 'ALL',
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
                $personService->save($person);
            }
        } else {
            //this is an edit so we need to create a person and add contact details
            $person['forename'] = $data['fields']['complainantForename'];
            $person['familyName'] = $data['fields']['complainantFamilyName'];

            $personId = $personService->save($person);

            $contactDetailsService = $this->getServiceLocator()
                ->get('DataServiceManager')
                ->get('Generic\Service\Data\ContactDetails');

            $contactDetails = [
                'person' => $personId,
                'contactType' => 'ct_complainant'
            ];

            $contactDetailsId = $contactDetailsService->save($contactDetails);

            $data['fields']['complainantContactDetails'] = $contactDetailsId;
        }

        return parent::processSave($data);
    }
}
