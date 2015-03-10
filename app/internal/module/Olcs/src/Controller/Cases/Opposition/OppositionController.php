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

    const OPPTYPE_ENVIRONMENTAL_OBJECTION = 'otf_eob';
    const OPPTYPE_REPRESENTATION = 'otf_rep';

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
                'children' => array(
                    'operatingCentres',
                    'publicationLinks' => array(
                        'children' => array(
                            'publication'
                        )
                    )
                )
            ),
            'oppositionType',
            'opposer' => array(
                'children' => array(
                    'opposerType',
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

    /**
     * @var array
     */
    protected $inlineScripts = ['table-actions'];

    public function indexAction()
    {
        $view = $this->getView([]);

        $this->checkForCrudAction(null, [], $this->getIdentifierName());

        $this->buildTableIntoView();

        //we will already have list data
        $listData = $this->getListData();

        $viewVars = [
            'oooDate' => null,
            'oorDate' => null
        ];

        $case = $this->getCase();

        if (!empty($case['application'])) {
            $dateUtilityService = $this->getServiceLocator()->get('Olcs\Service\Utility\DateUtility');
            $viewVars['oorDate'] = $dateUtilityService->calculateOor($case['application']);
            $viewVars['oooDate'] = $dateUtilityService->calculateOoo($case['application']);
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

    public function processLoad($data)
    {
        if (isset($data['id'])) {
            $service = $this->getServiceLocator()->get(
                'DataServiceManager'
            )->get('Olcs\Service\Data\Mapper\Opposition');

            $case = $this->getCase();
            return $service->formatLoad($data, ['case' => $case]);
        } else {
            return parent::processLoad($data);
        }
    }

    public function processSave($data)
    {
        $service = $this->getServiceLocator()->get('DataServiceManager')->get('Olcs\Service\Data\Mapper\Opposition');

        $case = $this->getCase();

        $oppositionData = $service->formatSave($data, ['case' => $case]);

        parent::processSave($oppositionData);

        return $this->redirectToIndex();
    }

    /**
     * Alters form
     *
     * @param \Common\Controller\Form $form
     * @return \Common\Controller\Form
     */
    public function alterForm($form)
    {
        $case = $this->getCase();

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

        $oorDate = $dateUtilityService->calculateOor($case['application']);
        $oooDate = $dateUtilityService->calculateOoo($case['application']);

        $oorObj = new \DateTime($oorDate);
        $oooObj = new \DateTime($oooDate);

        $oorString = !empty($oorObj) ? $oorObj->format('d/m/Y') : '';
        $oooString = !empty($oooObj) ? $oooObj->format('d/m/Y') : '';

        $form->get('fields')
            ->get('outOfRepresentationDate')
            ->setLabel('Out of representation ' . $oorString);

        $form->get('fields')
            ->get('outOfObjectionDate')
            ->setLabel('Out of objection ' . $oooString);

        return $form;
    }
}
