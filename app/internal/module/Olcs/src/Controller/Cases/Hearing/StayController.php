<?php

/**
 * Case Stay Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Cases\Hearing;

// Olcs
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;
use Common\Exception\BadRequestException;

/**
 * Case Stay Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class StayController extends OlcsController\CrudAbstract
{
    use ControllerTraits\CaseControllerTrait;
    use ControllerTraits\HearingAppealControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'stay';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'stay';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'case-stay';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case';

    /**
     * For most case crud controllers, we use the view-new/layouts/case-inner-layout
     * layout file. Except submissions.
     *
     * @var string
     */
    protected $pageLayoutInner = 'view-new/layouts/case-inner-layout';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Stay';

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
            'stayType' => array(
                'properties' => array(
                    'id',
                    'description'
                )
            ),
            'outcome' => array(
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

    /**
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = array('forms/hearings-appeal');

    /**
     * Add action. First checks if stay type already exists
     *
     * @return \Zend\View\Model\ViewModel
     * @throws BadRequestException
     */
    public function addAction()
    {
        $stayType = $this->params()->fromRoute('stayType');
        $caseId = $this->getCase()['id'];

        $stayRecords = $this->getStayData($caseId);
        if (empty($stayRecords[$stayType])) {
            return parent::addAction();
        } else {
            throw new BadRequestException('Stay already exists');
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
        return $this->redirectToRoute(
            'case_hearing_appeal',
            ['action' => 'details'],
            [],
            true
        );
    }

    /**
     * Override processLoad to extract stay data and use as $data for a
     * given stay type
     *
     * @param array $data
     * @return array
     */
    public function processLoad($data)
    {
        $stayType = $this->params()->fromRoute('stayType');
        $caseId = $this->getCase()['id'];

        // check an appeal exists
        $appeal = $this->getAppealData($caseId);
        if (empty($appeal)) {
            throw new BadRequestException('Case has no appeal');
        }

        $stayRecords = $this->getStayData($caseId);
        if (!(empty($stayRecords[$stayType]))) {
            $data = $stayRecords[$stayType][0];
        }

        $data = $this->callParentProcessLoad($data);
        if (!empty($data['fields']['withdrawnDate'])) {
            $data['fields']['isWithdrawn'] = 'Y';
        }
        $data['fields']['stayType'] = $stayType;

        return $data;
    }

    /**
     * @codeCoverageIgnore Calls parent method
     * Call parent process load and return result. Public method to allow unit testing
     *
     * @param array $data
     * @return array
     */
    public function callParentProcessLoad($data)
    {
        return parent::processLoad($data);
    }

    /**
     * Override Save data to set the isWithdrawn flag
     *
     * @param array $data
     * @param string $service
     * @return array
     */
    public function save($data, $service = null)
    {
        // modify $data
        if (isset($data['isWithdrawn']) && $data['isWithdrawn'] == 'N') {
            $data['withdrawnDate'] = null;
        }

        $data = $this->callParentSave($data, $service);

        return $data;
    }

    /**
     * @codeCoverageIgnore Calls parent method
     * Call parent process load and return result. Public method to allow unit testing
     *
     * @param array $data
     * @return array
     */
    public function callParentSave($data, $service = null)
    {
        return parent::save($data, $service);
    }
}
