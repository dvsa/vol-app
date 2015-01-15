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
    protected $formName = 'TmCaseRepute';

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
            'decision' => array(
                'properties' => 'ALL'
            ),
            'rehab' => array(
                'properties' => 'ALL'
            ),
            'unfitness' => array(
                'properties' => 'ALL'
            )
        )
    );

    public function detailsAction()
    {
        $this->identifierName = 'case';
        $this->identifierKey = 'case';

        return parent::detailsAction();
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
}
