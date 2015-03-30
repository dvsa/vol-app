<?php
/**
 * History Controller
 */
namespace Olcs\Controller\Bus\Processing;

// Olcs
use Olcs\Controller\CrudAbstract;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Controller\Traits\CaseControllerTrait;

/**
 * History Controller
 */
class HistoryController extends CrudAbstract implements BusRegControllerInterface
{
    use CaseControllerTrait;

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
    protected $pageLayout = 'bus-registrations-section';

    /**
     * For most case crud controllers, we use the layout/case-details-subsection
     * layout file. Except submissions.
     *
     * @var string
     */
    protected $pageLayoutInner = 'layout/bus-registration-subsection';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'BusRegHistoryView';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'licence_bus_processing_event-history';

    protected $detailsView = 'pages/event-history';

    protected $defaultTableSortField = 'eventDatetime';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = [
        'busRegId',
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
     * Holds the table name
     *
     * @var string
     */
    protected $tableName = 'event-history';

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'children' => array(
            'eventHistoryType' => [],
            'user' => [
                'children' => [
                    'contactDetails' => [
                        'children' => [
                            'person' => [],
                        ]
                    ]
                ]
            ]
        )
    );
}
