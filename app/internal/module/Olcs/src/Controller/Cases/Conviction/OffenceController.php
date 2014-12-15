<?php

/**
 * Case Prohibition Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Craig Reasbeck <Craig.Reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Conviction;

use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Case Prohibition Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Craig Reasbeck <Craig.Reasbeck@valtech.co.uk>
 */
class OffenceController extends OlcsController\CrudAbstract
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'offence';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'legacyOffences';

    /**
     * Name of comment box field.
     *
     * @var string
     */
    protected $commentBoxName = '';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'offence';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case';

    protected $detailsView = 'view-new/pages/case/offence';

    /**
     * For most case crud controllers, we use the view-new/layouts/case-inner-layout
     * layout file. Except submissions.
     *
     * @var string
     */
    protected $pageLayoutInner = 'view-new/layouts/case-inner-layout';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'LegacyOffence';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_details_legacy_offence';

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
        /**
         * @todo [OLCS-5306] check this, it appears to be an invalid part of the bundle
        'children' => array(
            'case' => array(
                'properties' => array(
                    'id'
                )
            ),
            'prohibitionType' => array(
                'properties' => array(
                    'id',
                    'description'
                )
            )
        )
         */
    );

    /**
     * Contains the name of the view placeholder for the table.
     *
     * @var string
     */
    protected $tableViewPlaceholderName = 'table';
}
