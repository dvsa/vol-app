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
    implements OlcsController\Interfaces\CaseControllerInterface
{
    use ControllerTraits\CaseControllerTrait;
    use ControllerTraits\HearingAppealControllerTrait;

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
     * represented by a single navigation id.
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

    /**
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = array('forms/hearings-appeal');

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
        return $this->redirectToRouteAjax(
            'case_hearing_appeal',
            ['action' => 'details'],
            [],
            true
        );
    }

    /**
     * Map the data on load maps the isWithdrawn flag
     *
     * @param array $data
     * @return array
     */
    public function processLoad($data)
    {
        $data = $this->callParentProcessLoad($data);
        if (!empty($data['fields']['withdrawnDate'])) {
            $data['fields']['isWithdrawn'] = 'Y';
        }

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
