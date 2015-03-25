<?php
/**
 * History Controller
 */
namespace Olcs\Controller\Cases\Processing;

// Olcs
use Olcs\Controller\CrudAbstract;
use Olcs\Controller\Traits\CaseControllerTrait;
use Olcs\Controller\Interfaces\CaseControllerInterface;

/**
 * History Controller
 */
class HistoryController extends CrudAbstract implements CaseControllerInterface
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
    protected $service = 'EventHistory';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_processing_history';

    protected $detailsView = 'pages/event-history';

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
