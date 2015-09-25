<?php

/**
 * Applied Penalty Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Cases\Penalty;

use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Zend\View\Model\ViewModel;

/**
 * Applied Penalty Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class AppliedPenaltyController extends OlcsController\CrudAbstract implements CaseControllerInterface
{
    use ControllerTraits\CaseControllerTrait;

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
        'children' => array(
            'siPenaltyType' => array(),
            'seriousInfringement' => array()
        )
    );

    protected $addContentTitle = 'Add ERRU penalty';
    protected $editContentTitle = 'Edit ERRU penalty';

    /**
     * Simple redirect to index.
     */
    public function redirectToIndex()
    {
        return $this->redirectToRouteAjax(
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
