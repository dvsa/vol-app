<?php

/**
 * Case Conviction Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Conviction;

//use Olcs\Controller\Traits\DeleteActionTrait;
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Case Conviction Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class ConvictionController extends OlcsController\CrudAbstract
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'conviction';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'conviction';

    /**
     * Name of comment box field.
     *
     * @var string
     */
    protected $commentBoxName = 'convictionNote';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'conviction';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case';

    protected $pageLayoutInner = 'case/inner-layout';

    protected $defaultTableSortField = 'convictionDate';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Conviction';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_details_convictions';

    /**
     * Holds an array of variables for the default
     * index list page.
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
                'fields'
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
            'case' => array(
                'properties' => 'ALL',
                'children' => array(
                    'licence' => array(
                        'properties' => 'ALL',

                        'children' => array(
                            'organisation' => array(
                                'properties' => 'ALL'
                            ),
                        ),
                    ),
                ),
            ),
            'convictionCategory' => array(
                'properties' => array(
                    'id',
                    'description'
                ),
                'children' => array(
                    'parent' => array(
                        'properties' => array(
                            'id',
                            'description'
                        )
                    )
                )
            ),
            'defendantType' => array(
                'properties' => 'ALL'
            ),
        )
    );

    /**
     * @var array
     */
    protected $inlineScripts = ['showhideinput', 'conviction'];

    /**
     * Override Save data to set the operator name field if defendant type is operator
     *
     * @param array $data
     * @param string $service
     * @return array
     */
    public function save($data, $service = null)
    {
        // modify $data
        $case = $this->getCase();

        if (isset($data['defendantType']) && $data['defendantType'] == 'def_t_op') {
            $data['operatorName'] = $case['licence']['organisation']['name'];
        }

        $data = $this->callParentSave($data, $service);

        return $data;
    }

    /**
     * @codeCoverageIgnore Calls parent method
     * Call parent process load and return result. Public method to allow unit testing
     *
     * @param $data
     * @param null $service
     * @return array
     */
    public function callParentSave($data, $service = null)
    {
        return parent::save($data, $service);
    }
}
