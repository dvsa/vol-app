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
use Common\Exception\BadRequestException;

/**
 * Case Appeal Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class AppealController extends OlcsController\CrudAbstract
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
            'outcome' => array(
                'properties' => array(
                    'id',
                    'description'
                )
            ),
            'reason' => array(
                'properties' => array(
                    'id',
                    'description'
                )
            ),
            'case' => array(
                'properties' => array(
                    'id'
                )
            )
        )
    );

    public function addAction()
    {
        $caseId = $this->getCase()['id'];
        $appeal = $this->getAppealData($caseId);
        if (empty($appeal)) {
            return parent::addAction();
        } else {
            throw new BadRequestException('Case already has an appeal');
        }
    }

    /**
     * Override to ensure any form submit redirects to alternative controller
     * details action.
     *
     * @return mixed|\Zend\Http\Response
     */
    public function indexAction()
    {
        return $this->redirectToIndex();
    }

    /**
     * Override to ensure any form submit redirects to alternative controller
     * details action.
     *
     * @return mixed|\Zend\Http\Response
     */
    public function redirectToIndex()
    {
        return $this->redirectToRoute('case_hearing_appeal',
            ['action' => 'details'], [], true);
    }

    /**
     * Retrieves appeal data
     *
     * @param int $caseId
     * @return array
     */
    public function getAppealData($caseId)
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
