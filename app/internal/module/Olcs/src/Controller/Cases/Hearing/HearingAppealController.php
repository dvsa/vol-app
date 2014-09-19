<?php

/**
 * Case Hearing Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Cases\Hearing;

// Olcs
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Case Hearing Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class HearingAppealController extends OlcsController\CrudAbstract
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'appeal';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'appeal';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'appeal';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case';

    /**
     * For most case crud controllers, we use the case/inner-layout
     * layout file. Except submissions.
     *
     * @var string
     */
    protected $pageLayoutInner = 'case/inner-layout';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Appeal';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_hearings_appeals_stays';

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
                'details',
                'fields',
                'base',
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
            'case' => array(
                'properties' => array(
                    'id'
                )
            ),
            'appeal' => array(
                'properties' => 'ALL'
            )
        )
    );

    /**
     * Holds the details view
     *
     * @return array|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    protected $detailsView = '/case/hearing-appeal/details';

    public function indexAction()
    {
        return $this->redirectToIndex();
    }

    public function redirectToIndex()
    {
        return $this->redirectToRoute(null, ['action' => 'details'], [], true);
    }

    public function detailsAction()
    {
        $caseId = $this->getCase()['id'];
        $appeal = $this->getAppealData($caseId);
        $stayRecords = $this->getStayData($caseId);

        $view = $this->getView([]);

        $this->getViewHelperManager()
            ->get('placeholder')
            ->getContainer($this->getIdentifierName())
            ->set($appeal);

        $view->setTemplate($this->detailsView);
        $view->setVariable('case', $this->getCase());

        return $this->renderView($view);
    }

    /**
     * Gets stay data for use on the index page
     *
     * @param int $caseId
     * @return array
     */
    private function getStayData($caseId)
    {
        $stayRecords = array();

        $stayResult = $this->makeRestCall('Stay', 'GET', array('case' => $caseId));

        //need a better way to do this...
        foreach ($stayResult['Results'] as $stay) {
            if (isset($this->stayTypes[$stay['stayType']])) {
                $stay = $this->formatDates(
                    $stay,
                    array(
                        'requestDate',
                        'withdrawnDate'
                    )
                );

                $stayRecords[$stay['stayType']] = $stay;
            }
        }

        return $stayRecords;
    }

    /**
     * Retrieves appeal data
     *
     * @param int $caseId
     * @return array
     */
    private function getAppealData($caseId)
    {
        $bundle = [
            'children' => [
                'reason' => [
                    'properties' => [
                        'id',
                        'description'
                    ]
                ],
                'outcome' => [
                    'properties' => [
                        'id',
                        'description'
                    ]
                ]
            ],
        ];

        $appealResult = $this->makeRestCall(
            'Appeal',
            'GET',
            array(
                'case' => $caseId,
                'bundle' => json_encode($bundle)
            )
        );

        $appeal = array();

        if (!empty($appealResult['Results'][0])) {
            $appeal = $this->formatDates(
                $appealResult['Results'][0],
                array(
                    'deadlineDate',
                    'appealDate',
                    'hearingDate',
                    'decisionDate',
                    'papersDueDate',
                    'papersSentDate',
                    'withdrawnDate'
                )
            );
        }

        return $appeal;
    }

    /**
     * Formats the specified fields in the supplied array with the correct date format
     * Expect to replace this with a view helper later
     *
     * @param array $data
     * @param array $fields
     * @return array
     */
    private function formatDates($data, $fields)
    {
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = date('d/m/Y', strtotime($data[$field]));
            }
        }

        return $data;
    }
}
