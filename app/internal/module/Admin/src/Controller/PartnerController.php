<?php
/**
 * Partner Controller
 */

namespace Admin\Controller;

use Olcs\Controller\CrudAbstract;

/**
 * Partner Controller
 *
 * @author  Valtech <uk@valtech.co.uk>
 */

class PartnerController extends CrudAbstract
{
    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'id';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'partner';

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
    protected $formName = 'admin-partner';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'admin-partner-section';

    protected $pageLayoutInner = null;

    protected $defaultTableSortField = 'id';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'ContactDetails';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-user-management';

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
            'contactType' => [],
            'address' => [],
        ]
    );

    /**
     * Entity display name (used by confirm plugin via deleteActionTrait)
     * @var string
     */
    protected $entityDisplayName = 'Partner';

    /**
     * Index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $this->getViewHelperManager()->get('placeholder')->getContainer('pageTitle')->append('Partners');

        return parent::indexAction();
    }

    /**
     * Gets table params
     *
     * @return array
     */
    public function getTableParams()
    {
        $params = parent::getTableParams();

        $extraParams = [
            'contactType' => 'IN ["ct_partner"]',
        ];

        return array_merge($params, $extraParams);
    }

    /**
     * Redirect action
     *
     * @return \Zend\Http\Response
     */
    public function redirectAction()
    {
        return $this->redirectToRouteAjax(
            'admin-dashboard/admin-partner-management',
            ['action'=>'index', $this->getIdentifierName() => null],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }

    /**
     * Gets the publication service
     *
     * @return mixed
     */
    /*private function getPartnerService()
    {
        return $this->getServiceLocator()->get('DataServiceManager')->get('Common\Service\Data\Partner');
    }*/
}
