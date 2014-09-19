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

/**
 * Case Stay Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class StayController extends OlcsController\CrudAbstract
{
    use ControllerTraits\CaseControllerTrait;

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
            'case' => array(
                'properties' => array(
                    'id'
                )
            )
        )
    );

    /**
     * Holds the Stay Data Bundle
     *
     * @var array
     */
    protected $stayDataBundle = array(
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


    public function indexAction()
    {
        return $this->redirectToIndex();
    }

    public function redirectToIndex()
    {
        return $this->redirectToRoute('case_hearing_appeal',
            ['action' => 'details'], [], true);
    }

    public function processLoad($data)
    {
        $data = parent::processLoad($data);
        $data['fields']['stayType'] = $this->params()->fromRoute('stayType');
        return $data;
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

        $stayResult = $this->makeRestCall('Stay', 'GET',
            array('case' => $caseId), $this->stayDataBundle);

        //need a better way to do this...
        foreach ($stayResult['Results'] as $stay) {
            $stayRecords[$stay['stayType']['id']][] = $stay;
        }

        return $stayRecords;
    }
}
