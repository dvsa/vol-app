<?php
/**
 * Publication Controller
 */

namespace Admin\Controller;

use Olcs\Controller\CrudAbstract;

/**
 * Publication Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class PublicationController extends CrudAbstract
{

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'publication';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'admin-publication';

    /**
     * Name of comment box field.
     *
     * @var string
     */
    protected $commentBoxName = null;

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'publication';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'admin-publication-section';

    protected $pageLayoutInner = null;

    protected $defaultTableSortField = 'id';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Publication';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-publication';

    /**
     * Holds an array of variables for the default
     * index list page.
     */
    protected $listVars = [];

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
        'children' => [
                'pubStatus' => [
                    'properties' => 'ALL',
            ],
            'trafficArea' => [
                'properties' => 'ALL',
            ]
        ]
    );

    /**
     * @var array
     */
    protected $inlineScripts = ['table-actions'];

    /**
     * Entity display name (used by confirm plugin via deleteActionTrait)
     * @var string
     */
    protected $entityDisplayName = 'Publication';

    public function indexAction()
    {
        $this->getViewHelperManager()->get('placeholder')->getContainer('pageTitle')->append('Publications');

        return parent::indexAction();
    }

    public function getTableParams()
    {
        $params = parent::getTableParams();

        $extraParams = [
            'pubStatus' => $this->getQueryOrRouteParam('pub_s_new', 'pub_s_new'),
        ];

        return array_merge($params, $extraParams);
    }
}
