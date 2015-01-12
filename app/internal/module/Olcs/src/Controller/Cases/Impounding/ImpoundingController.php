<?php

/**
 * Case Impounding Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Cases\Impounding;

// Olcs
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Case Impounding Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ImpoundingController extends OlcsController\CrudAbstract
    implements OlcsController\Interfaces\CaseControllerInterface
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'impounding';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'impounding';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'impounding';

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
    protected $service = 'Impounding';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_details_impounding';

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
            'case' => array(
                'properties' => array(
                    'id'
                )
            ),
            'presidingTc' => array(
                'properties' => array(
                    'id',
                    'name'
                )
            ),
            'outcome' => array(
                'properties' => array(
                    'id',
                    'name'
                )
            ),
            'impoundingType' => array(
                'properties' => array(
                    'id',
                    'description'
                )
            ),
            'piVenue' => array(
                'properties' => array(
                    'id',
                    'name'
                )
            ),
            'impoundingLegislationTypes' => array(
                'properties' => 'ALL'
            ),
        )
    );

    /**
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = array('forms/impounding');

    /**
    * Overrides the parent, needed to make absolutely sure we can't have data in both venue fields :)
    *
    * @param array $data
    * @return \Zend\Http\Response
    */
    public function processSave($data)
    {
        if ($data['fields']['piVenue'] != 'other') {
            $data['fields']['piVenueOther'] = null;
        }

        return parent::processSave($data);
    }

    public function processLoad($data)
    {
        $data = parent::processLoad($data);

        if (isset($data['fields']['piVenueOther']) && $data['fields']['piVenueOther'] != '') {
            $data['fields']['piVenue'] = 'other';
        }

        return $data;
    }
}
