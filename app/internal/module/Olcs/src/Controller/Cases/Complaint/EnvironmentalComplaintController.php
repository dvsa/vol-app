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

/**
 * Case EnvironmentalComplaint Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */
class EnvironmentalComplaintController extends OlcsController\CrudAbstract implements
    OlcsController\Interfaces\CaseControllerInterface
{
    use ControllerTraits\CaseControllerTrait;
    use ControllerTraits\GenerateActionTrait;

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
    protected $pageLayoutInner = 'layout/wide-layout';

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
    protected $navigationId = 'case_opposition';

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
            'ocComplaints' => array(
                'children' => array(
                    'operatingCentre'
                )
            ),
            'complainantContactDetails' => [
                'children' => [
                    'address' => array(
                        'children' => array(
                            'countryCode' => array()
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
     * @var int $licenceId cache of licence id for a given case
     */
    protected $licenceId;

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

        if (isset($data['closedDate'])) {
            $data['status'] = 'ecst_closed';
        } else {
            $data['status'] = 'ecst_open';
        }

        $ocComplaints = [];

        if (isset($data['ocComplaints'])) {
            foreach ($data['ocComplaints'] as $ocComplaint) {
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
        $response = $this->getServiceLocator()->get('BusinessServiceManager')
            ->get('Cases\Complaint\EnvironmentalComplaint')
            ->process(
                [
                    'id' => $this->getIdentifier(),
                    'data' => $data['fields'],
                    'address' => $data['address'],
                    'caseId' => $this->getFromRoute('case'),
                ]
            );

        if ($response->isOk()) {
            $this->addSuccessMessage('Saved successfully');
            $this->setIsSaved(true);
            return $this->redirectToIndex();
        }
    }

    /**
     * Redirect to oppositions page which shows list of env complaints.
     */
    public function redirectToIndex()
    {
        return $this->redirectToRouteAjax(
            'case_opposition',
            ['action'=>'index', 'case' => $this->params()->fromRoute('case')],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            false
        );
    }

    /**
     * Route for document generate action redirects
     * @see Olcs\Controller\Traits\GenerateActionTrait
     * @return string
     */
    protected function getDocumentGenerateRoute()
    {
        return 'case_licence_docs_attachments/entity/generate';
    }

    /**
     * Route params for document generate action redirects
     * @see Olcs\Controller\Traits\GenerateActionTrait
     * @return array
     */
    protected function getDocumentGenerateRouteParams()
    {
        return [
            'case' => $this->getFromRoute('case'),
            'licence' => $this->getLicenceIdForCase(),
            'entityType' => 'complaint',
            'entityId' => $this->getFromRoute('complaint')
        ];
    }

    /**
     * Gets licence id from route or backend, caching it in member variable
     */
    protected function getLicenceIdForCase()
    {
        if (is_null($this->licenceId)) {
            $this->licenceId = $this->getQueryOrRouteParam('licence');
            if (empty($this->licenceId)) {
                $case = $this->getCase();
                $this->licenceId = $case['licence']['id'];
            }
        }
        return $this->licenceId;
    }
}
