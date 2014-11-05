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
use Common\Service\Data\SlaServiceAwareTrait;

use Zend\View\Model\ViewModel;

/**
 * Case Complaint Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */
class PublicInquiryController extends OlcsController\CrudAbstract
{
    use ControllerTraits\CaseControllerTrait;
    use SlaServiceAwareTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'case';

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
    protected $navigationId = 'case_hearings_appeals_public_inquiry';

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

    protected $isListResult = true;
    protected $identifierKey = 'case';
    protected $placeholderName = 'pi';

    public function redirectToIndex()
    {
        return $this->redirectToRoute(
            'case_pi',
            ['action'=>'details'],
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

    public function detailsAction()
    {
        $pi = $this->loadCurrent();

        if (!empty($pi)) {

            $pi = $this->setupSla($pi);

            if ($this->getRequest()->isPost()) {
                $action = strtolower($this->getFromPost('action'));
                $id = $this->getFromPost('id');

                if (!($action == 'edit' && !is_numeric($id))) {
                    //if we have an add action make sure there's no row selected
                    if ($action == 'add') {
                        $id = null;
                    }

                    return $this->redirectToRoute(
                        'case_pi_hearing',
                        ['action' => $action, 'id' => $id, 'pi' => $pi['id']],
                        ['code' => '303'], // Why? No cache is set with a 303 :)
                        true
                    );
                }
            }

            $this->forward()->dispatch(
                'PublicInquiry\HearingController',
                array(
                    'action' => 'index',
                    'case' => $this->getFromRoute('case'),
                    'pi' => $pi['id']
                )
            );
        }

        $view = $this->getView([]);

        $this->getViewHelperManager()
            ->get('placeholder')
            ->getContainer($this->getPlaceholderName())
            ->set($pi);

        $this->getViewHelperManager()
            ->get('placeholder')
            ->getContainer('details')
            ->set($pi);

        $view->setTemplate('case/page/pi');

        return $this->renderView($view);
    }

    public function setupSla($pi)
    {
        $pi = $this->formatDataForSlaService($pi);

        $this->setSlaService($this->getServiceLocator()->get('Common\Service\Data\Sla'));

        $this->getSlaService()->setContext('pi', $pi);

        $businessRules = $this->getSlaService()->fetchBusRules('pi');
        $businessRules = array_map(
            function ($item) {
                return $item['field'];
            },
            $businessRules
        );

        foreach (array_keys($pi) as $key) {
            if (in_array($key, $businessRules)) {
                $tKey = $key . 'Target';
                $pi[$tKey] = $this->getSlaService()->getTargetDate('pi', $key);
            }
        }

        return $pi;
    }

    public function formatDataForSlaService($data)
    {
        if (isset($data['piHearings']) && is_array($data['piHearings']) && count($data['piHearings']) > 0) {

            $hearing = end($data['piHearings']);

            if ($hearing['isAdjourned'] != 'Y' && $hearing['isCancelled'] != 'Y') {

                $data['hearingDate'] = $hearing['hearingDate'];
            }

        }

        return $data;
    }
}
