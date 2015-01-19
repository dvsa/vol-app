<?php

/**
 * Processing Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Processing;

// Olcs
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;
use Olcs\Controller\Interfaces\CaseControllerInterface;

/**
 * Case Decisions Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class DecisionsController extends OlcsController\CrudAbstract implements CaseControllerInterface
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'id';

    /**
     * Identifier key
     *
     * @var string
     */
    protected $identifierKey = 'id';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = '';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case-section';

    /**
     * For most case crud controllers, we use the layout/case-details-subsection
     * layout file. Except submissions.
     *
     * @var string
     */
    protected $pageLayoutInner = 'layout/case-details-subsection';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'TmCaseDecision';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_processing_decisions';

    protected $detailsView = 'pages/case/tm-decision';

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
            'decision' => [],
            'rehabMeasures' => ['id', 'description'],
            'unfitnessReasons' => ['id', 'description'],
            'case' => []
        )
    );

    public function detailsAction()
    {
        $this->identifierName = 'case';
        $this->identifierKey = 'case';

        return parent::detailsAction();
    }

    protected function getFormName()
    {
        $decisionType = $this->params()->fromRoute('decision');

        switch ($decisionType) {
            case 'tm_decision_rl':
                //unfit
                $this->setFormName('TmCaseUnfit');
                break;
            case 'tm_decision_rnl':
                //repute
                $this->setFormName('TmCaseRepute');
                break;
            default:
                //throw exception
        }

        return parent::getFormName();
    }

    /**
     * Get data for form
     *
     * @return array
     */
    public function getDataForForm()
    {
        $data = parent::getDataForForm();

        $data['fields']['decision'] = $this->getFromRoute('decision');

        return $data;
    }


    public function redirectToIndex()
    {
        return $this->redirectToRouteAjax(
            'processing_decisions',
            ['action'=>'details', 'case' => $this->params()->fromRoute('case')],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            false
        );
    }
}
