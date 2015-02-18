<?php

/**
 * Case Conviction Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Conviction;

use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;
use Olcs\Controller\Interfaces\CaseControllerInterface;

/**
 * Case Conviction Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class ConvictionController extends OlcsController\CrudAbstract implements CaseControllerInterface
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
    protected $pageLayout = 'case-section';

    protected $pageLayoutInner = 'layout/case-details-subsection';

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
    protected $inlineScripts = ['conviction', 'table-actions'];

    /**
     * Entity display name (used by confirm plugin via deleteActionTrait)
     * @var string
     */
    protected $entityDisplayName = 'conviction';

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

        if (isset($data['defendantType'])) {
            if ($data['defendantType'] == 'def_t_op') {
                //set organisation name, remove person name
                $data['operatorName'] = $case['licence']['organisation']['name'];
                $data['personFirstname'] = '';
                $data['personLastname'] = '';
            } else {
                //this is a person name so remove operator name
                $data['operatorName'] = '';
            }
        }

        $data = parent::save($data, $service);

        return $data;
    }
}
