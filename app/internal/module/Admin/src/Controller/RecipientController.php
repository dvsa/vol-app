<?php
/**
 * Recipient Controller
 */

namespace Admin\Controller;

use Olcs\Controller\CrudAbstract;

/**
 * Recipient Controller
 */

class RecipientController extends CrudAbstract
{

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'recipient';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'recipient';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'recipient';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'admin-publication-section';

    protected $pageLayoutInner = 'layout/wide-layout';

    protected $defaultTableSortField = 'contactName';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Recipient';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-publication/recipient';

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
            'trafficAreas' => array(),
        )
    );

    /**
     * @var array
     */
    protected $inlineScripts = ['table-actions'];

    /**
     * Entity display name (used by confirm plugin via deleteActionTrait)
     * @var string
     */
    protected $entityDisplayName = 'Recipient';
}
