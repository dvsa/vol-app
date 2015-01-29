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
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Common\Exception\BadRequestException;

/**
 * Case Opposition Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class OppositionController extends OlcsController\CrudAbstract implements CaseControllerInterface
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
    protected $pageLayout = 'case-section';

    protected $pageLayoutInner = 'layout/wide-layout';

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

    protected $identifierName = 'opposition';

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
                    'opposerType' => array(
                        'id',
                        'description'
                    ),
                    'contactDetails' => array(
                        'children' => array(
                            'person',
                            'address' => array(
                                'children' => array(
                                    'countryCode'
                                )
                            ),
                            'phoneContacts'
                        )
                    )
                )
            ),
            'grounds' => array(
                'properties' => array(
                    'id',
                    'description'
                )
            ),
            'operatingCentres' => array()
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
            'complainantContactDetails' => [
                'children' => [
                    'person'
                ]
            ],
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
        $view->setTemplate('pages/case/opposition');

        return $this->renderView($view);
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

    public function processLoad($data)
    {
        if (isset($data['id'])) {
            $service = $this->getServiceLocator()->get(
                'DataServiceManager'
            )->get('Olcs\Service\Data\Mapper\Opposition');

            $caseId = $this->params()->fromRoute('case');
            $case = $this->getCase($caseId);
            return $service->formatLoad($data, ['case' => $case]);
        } else {
            return parent::processLoad($data);
        }
    }

    public function processSave($data)
    {
        $service = $this->getServiceLocator()->get('DataServiceManager')->get('Olcs\Service\Data\Mapper\Opposition');

        $caseId = $this->params()->fromRoute('case');
        $case = $this->getCase($caseId);

        $oppositionData = $service->formatSave($data, ['case' => $case]);

        parent::processSave($oppositionData);

        return $this->redirectToIndex();
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

    /**
     * Alters form
     *
     * @param \Common\Controller\Form $form
     * @return \Common\Controller\Form
     */
    public function alterForm($form)
    {
        $caseId = $this->params()->fromRoute('case');
        $case = $this->getCase($caseId);

        if ($case['licence']['goodsOrPsv']['id'] == 'lcat_psv') {
            $options = $form->get('fields')
                ->get('oppositionType')
                ->getValueOptions();
            unset($options['otf_eob']);
            unset($options['otf_rep']);
            $form->get('fields')
                ->get('oppositionType')
                ->setValueOptions($options);
        }

        $dateUtilityService = $this->getServiceLocator()->get('Olcs\Service\Utility\DateUtility');

        $form->get('fields')
            ->get('outOfRepresentationDate')
            ->setLabel('Out of representation ' . $dateUtilityService->calculateOor($case['application']));

        $form->get('fields')
            ->get('outOfObjectionDate')
            ->setLabel('Out of objection ' . $dateUtilityService->calculateOoo($case['application']));

        return $form;
    }
}
