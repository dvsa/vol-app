<?php

/**
 * Case Complaint Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace Olcs\Controller\Cases\PublicInquiry;

// Olcs
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

use Zend\View\Model\ViewModel;

/**
 * Case Complaint Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */
class PublicInquiryController extends OlcsController\CrudAbstract
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'pi';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'Pi';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case';

    protected $detailsView = 'case/page/pi';

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
    protected $service = 'Pi';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_details_public_inquiry';

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
    protected $dataBundle = [
        'children' => [
            'piStatus' => [
                'properties' => 'ALL',
            ],
            'piTypes' => [
                'properties' => 'ALL',
            ],
            'presidingTc' => [
                'properties' =>
                    [
                        'id',
                        'name'
                    ]
            ],
            'reasons' => [
                'properties' => 'ALL',
                'children' => [
                    'reason' => [
                        'properties' => 'ALL',
                    ]
                ],
            ],
            'piHearings' => array(
                'properties' => 'ALL',
                'children' => [
                    'presidingTc' => [
                        'properties' => 'ALL',
                    ],
                    'presidedByRole' => [
                        'properties' => 'ALL',
                    ],
                ],
            ),
            'writtenOutcome' => array(
                'properties' => 'ALL'
            ),
            'decidedByTc' => array(
                'properties' => 'ALL'
            ),
            'agreedByTc' => array(
                'properties' => 'ALL'
            ),
            'decidedByTcRole' => array(
                'properties' => 'ALL'
            ),
            'agreedByTcRole' => array(
                'properties' => 'ALL'
            ),
            'decisions' => array(
                'properties' => 'ALL'
            ),
            'assignedTo' => array(
                'properties' => 'ALL'
            ),
            'case' => array(
                'properties' => ['id']
            ),

        ]
    ];

    public function redirectToIndex()
    {
        return $this->redirectToRoute(
            'case_pi',
            ['action'=>'details', $this->getIdentifierName() => null],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }

    public function processDataMapForSave($oldData, $map = array(), $section = 'main')
    {
        $data = parent::processDataMapForSave($oldData, $map, $section);
        if (!isset($data['case']) || empty($data['case'])) {
            $data['case'] = $this->params()->fromRoute('case');
        }
        return $data;
    }

    /**
     * Load data for the form
     *
     * This method should be overridden
     *
     * @param int $id
     * @return array
     */
    protected function load($id)
    {
        if (empty($this->loadedData)) {
            $service = $this->getService();

            $result = $this->makeRestCall(
                $service,
                'GET',
                array('case' => $this->params()->fromRoute('case'), 'order'=>'id', 'limit'=>1),
                $this->getDataBundle()
            );

            if (empty($result) || !array_key_exists('Results', $result) ||empty($result['Results'])) {
                $this->setCaughtResponse($this->notFoundAction());
                return;
            }

            $this->loadedData = current($result['Results']);
        }

        return $this->loadedData;
    }
}
