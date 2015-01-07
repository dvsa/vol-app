<?php

/**
 * Case Opposition Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Olcs\Controller\Cases\Opposition;

// Olcs
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Case Opposition Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class OppositionController extends OlcsController\CrudAbstract
    implements OlcsController\Interfaces\CaseControllerInterface
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'opposition';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'opposition';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case';

    protected $pageLayoutInner = 'case/inner-layout';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Opposition';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_opposition';

    protected $identifierName = 'opposition_id';

    /**
     * Holds an array of variables for the default
     * index list page.
     */
    protected $listVars = [
        'case'
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
     * Holds the Data Bundle
     *
     * @var array
    */
    protected $dataBundle = array(
        'children' => array(
            'application' => array(
                'properties' => array(
                    'id',
                    'receivedDate'
                ),
                'children' => array(
                    'operatingCentres' => array(
                        'properties' => array(
                            'adPlacedDate'
                        )
                    )
                )
            ),
            'oppositionType' => array(
                'properties' => array(
                    'description'
                )
            ),
            'opposer' => array(
                'children' => array(
                    'contactDetails' => array(
                        'children' => array(
                            'person' => array(
                                'properties' => array(
                                    'forename',
                                    'familyName'
                                )
                            )
                        )
                    )
                )
            ),
            'grounds' => array(
                'children' => array(
                    'grounds' => array(
                        'properties' => array(
                            'id',
                            'description'
                        )

                    )
                )
            )
        )
    );

    /**
     * Holds the Data Bundle for environmental complaints
     *
     * @var array
     */
    protected $complaintsBundle = array(
        'properties' => 'ALL',
        'children' => [
            'status' => [],
            'complainantContactDetails' => [],
            'ocComplaints' => [
                'children' => [
                    'operatingCentre' => [
                        'children' => [
                            'address'
                        ]
                    ]
                ]
            ]
        ]
    );

    public function indexAction()
    {
        $view = $this->getView([]);

        $this->checkForCrudAction(null, [], $this->getIdentifierName());

        $this->buildTableIntoView();

        //we will already have list data
        $listData = $this->getListData();

        //operating centre is linked to the application so we only need to check the first one
        if (isset($listData['Results'][0]['application']['operatingCentres'][0]['adPlacedDate'])) {
            $operatingCentres = $listData['Results'][0]['application']['operatingCentres'];
            rsort($operatingCentres);

            $newspaperDate = $operatingCentres[0]['adPlacedDate'];
            $receivedDate = $listData['Results'][0]['application']['receivedDate'];

            $viewVars = $this->calculateDates($receivedDate, $newspaperDate);
        } else {
            $viewVars = [
                'oooDate' => null,
                'oorDate' => null
            ];
        }

        $environmentalTable = $this->getEnvironmentalComplaintsTable();

        $this->getViewHelperManager()->get('placeholder')->getContainer('complaintsTable')->set(
            $environmentalTable
        );

        $view->setVariables($viewVars);
        $view->setTemplate('case/page/opposition');

        return $this->renderView($view);
    }

    public function processSave($data)
    {
        $caseId = $this->params()->fromRoute('case');

        $case = $this->getCase($caseId);

        $oppositionData['application'] = $case['application']['id'];
        $oppositionData['licence'] = $case['licence']['id'];
        $oppositionData['case'] = $data['base']['case'];
        $oppositionData['isCopied'] = $data['fields']['isCopied'];
        $oppositionData['isInTime'] = $data['fields']['isInTime'];
        $oppositionData['isValid'] = $data['fields']['isValid'];
        $oppositionData['oppositionType'] = $data['fields']['oppositionType'];
        $oppositionData['raisedDate'] = $data['fields']['raisedDate'];
        $oppositionData['validNotes'] = $data['fields']['validNotes'];
        $oppositionData['grounds'] = $data['fields']['grounds'];

        // set up opposer
        $oppositionData['opposer']['opposerType'] = $data['fields']['opposerType'];

        // set up contactDetails
        unset($data['fields']['address']['searchPostcode']);
        $oppositionData['opposer']['contactDetails']['description'] = $data['fields']['contactDetailsDescription'];
        $oppositionData['opposer']['contactDetails']['address'] = $data['fields']['address'];
        $oppositionData['opposer']['contactDetails']['forename'] = $data['fields']['forename'];
        $oppositionData['opposer']['contactDetails']['familyName'] = $data['fields']['familyName'];
        $oppositionData['opposer']['contactDetails']['emailAddress'] = $data['fields']['emailAddress'];
        $oppositionData['opposer']['contactDetails']['contactType'] = 'ct_obj';

        // set up phone contact
        $phoneContact = array();
        $phoneContact['id'] = '';
        $phoneContact['phoneNumber'] = $data['fields']['phone'];
        $phoneContact['phoneContactType'] = 'phone_t_home';

        $oppositionData['opposer']['contactDetails']['phoneContacts'][0] = $phoneContact;

        $oppositionData['fields'] = $oppositionData;


        $result = parent::processSave($oppositionData, false);

        // set up operatingCentreOppositions
        $operatingCentreOppositions = array();
        if (is_array($data['fields']['affectedCentres'])) {
            $opposition_id = isset($result['id']) ? $result['id'] : $data['fields']['id'];
            foreach ($data['fields']['affectedCentres'] as $operatingCentreId) {
                $ocoParams = array('opposition' => $opposition_id);
                $ocoParams['operatingCentre'] = $operatingCentreId;
                $this->makeRestCall('OperatingCentreOpposition', 'POST', $ocoParams);
            }
        }

        return $this->redirectToIndex();

    }

    private function getEnvironmentalComplaintsTable()
    {
        $caseId = $this->params()->fromRoute('case');
        $tableName = 'environmental-complaints';
        $params = ['sort' => 'id', 'isCompliance' => 0, 'case' => $caseId];
        $data = $this->makeRestCall('Complaint', 'GET', $params, $this->getComplaintsBundle());

        return $this->alterTable($this->getTable($tableName, $data, $params));
    }

    public function getComplaintsBundle()
    {
        return $this->complaintsBundle;
    }


    private function calculateDates($applicationDate, $newsPaperDate)
    {
        $appDateObj = new \DateTime($applicationDate);
        $appDateObj->setTime(0, 0, 0); //is from a datetime db field - stop the time affecting the 21 day calculation
        $newsDateObj = new \DateTime($newsPaperDate);

        if ($appDateObj > $newsDateObj) {
            $oorDate = null;
        } else {
            $newsDateObj->add(new \DateInterval('P21D'));

            //we could format the date here but returning the date in ISO format
            //allows us to format the date using the configured view helper
            $oorDate = $newsDateObj->format(\DateTime::ISO8601);
        }

        return [
            'oooDate' => null,
            'oorDate' => $oorDate
        ];
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
