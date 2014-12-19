<?php

/**
 * Applied Penalty Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Cases\Penalty;

// Olcs
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Applied Penalty Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class AppliedPenaltyController extends OlcsController\CrudAbstract
    implements OlcsController\Interfaces\CaseControllerInterface
{
    use ControllerTraits\CaseControllerTrait;

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
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'erru-penalty';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'SiPenalty';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_details_penalties';

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
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'properties' => 'ALL',
        'children' => array(
            'siPenaltyType' => array(
                'properties' => array(
                    'id'
                )
            ),
            'seriousInfringement' => array(
                'properties' => array(
                    'id'
                )
            )
        )
    );

    /**
     * Simple redirect to index.
     */
    public function redirectToIndex()
    {
        return $this->redirectToRoute(
            'case_penalty',
            ['action'=>'index', 'case' => $this->params()->fromRoute('case')],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            false
        );
    }

    /**
     * Adds the serious infringement id into the form data
     *
     * @return array
     */
    public function getDataForForm()
    {
        $data = parent::getDataForForm();
        $data['fields']['seriousInfringement'] = $this->params()->fromRoute('seriousInfringement');

        return $data;
    }
}
