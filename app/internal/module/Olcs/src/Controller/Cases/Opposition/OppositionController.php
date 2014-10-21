<?php

/**
 * Case Opposition Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Olcs\Controller\Cases\Opposition;

// Olcs
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Case Opposition Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class OppositionController extends OlcsController\CrudAbstract
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'opposition';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'opposition';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case';

    protected $pageLayoutInner = 'case/inner-layout';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Opposition';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_opposition';

    /**
     * Holds an array of variables for the default
     * index list page.
     */
    protected $listVars = [
        'application'
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
            'application' => array(
                'properties' => array(
                    'id'
                )
            ),
            'opposer' => array(
                'children' => array(
                    'contactDetails' => array(
                        'children' => array(
                            'person' => array(
                                'properties' => array(
                                    'forename',
                                    'familyName'
                                )
                            )
                        )
                    )
                )
            ),
            'grounds' => array(
                'children' => array(
                    'grounds' => array(
                        'properties' => array(
                            'id',
                            'description'
                        )

                    )
                )
            )
        )
    );
}