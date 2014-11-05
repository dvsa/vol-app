<?php

namespace Olcs\Controller\Cases\PublicInquiry;

use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

use Zend\View\Model\ViewModel;

/**
 * Class HearingController
 * @package Olcs\Controller\Cases\PublicInquiry
 */
class HearingController extends OlcsController\CrudAbstract
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'id';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $tableName = 'piHearing';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'PublicInquiryHearing';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'PiHearing';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_hearings_appeals_public_inquiry';

    /**
     * For most case crud controllers, we use the case/inner-layout
     * layout file. Except submissions.
     *
     * @var string
     */
    protected $pageLayoutInner = 'case/inner-layout';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = [
        'case',
        'pi'
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
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = [
        'children' => [
            'piVenue' => [
                'properties' => [
                    'id',
                    'name'
                ],
            ],
            'presidingTc' => [
                'properties' => [
                    'id'
                ],
            ],
            'presidedByRole' => [
                'properties' => [
                    'id'
                ],
            ],
            'pi' => [
                'properties' => [
                    'id'
                ],
            ],
        ]
    ];

    public function redirectToIndex()
    {
        return $this->redirectToRoute(
            'case_pi',
            ['action'=>'details'],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }

    /**
     * Get data for form
     *
     * @return array
     */
    public function getDataForForm()
    {
        $data = parent::getDataForForm();
        $data['fields']['pi'] = $this->getFromRoute('pi');

        return $data;
    }

    /**
     * Overrides the parent, make sure there's nothing there shouldn't be in the optional fields
     *
     * @param array $data
     * @return \Zend\Http\Response
     */
    public function processSave($data)
    {
        if ($data['fields']['piVenue'] != 'other') {
            $data['fields']['piVenueOther'] = null;
        }

        if ($data['fields']['isCancelled'] != 'Y') {
            $data['fields']['cancelledReason'] = null;
            $data['fields']['cancelledDate'] = null;
        }

        if ($data['fields']['isAdjourned'] != 'Y') {
            $data['fields']['adjournedReason'] = null;
            $data['fields']['adjournedDate'] = null;
        }

        $this->addTask($data);

        return parent::processSave($data);
    }

    public function addTask(array $data)
    {
        if (isset($data['fields']) && is_array($data['fields'])) {
            $data = $data['fields'];
        }

        if ($data['isAdjourned'] == 'Y') {

            $task = [
                'assignedByUser' => $this->getLoggedInUser(),
                'assignedToUser' => $this->getLoggedInUser(),
                'assignedToTeam' => 2, // @NOTE: not stubbed yet
                'cases' => $this->getCase()['id']
            ];

            if (isset($this->getCase()['licence']['id'])) {
                $task['licence'] = $this->getCase()['licence']['id'];
            }

            if (isset($this->getCase()['licence']['application']['id'])) {
                $task['applciation'] = $this->getCase()['licence']['application']['id'];
            }

            $task['description'] = 'Verify adjournment of case';
            $task['actionDate'] = date(
                'Y-m-d',
                mktime(date("H"), date("i"), date("s"), date("n"), date("j")+7, date("Y"))
            );
            $task['urgent'] = '1';
            $task['category'] = '2';
            $task['taskSubCategory'] = '81';

            $service = $this->getTaskService();
            $service->create($task);
        }
    }

    /**
     * @return \Common\Service\Data\Task
     */
    public function getTaskService()
    {
        return $this->getServiceLocator()->get('DataServiceManager')->get('Common\Service\Data\Task');
    }
}
