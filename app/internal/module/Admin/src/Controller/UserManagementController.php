<?php
/**
 * User Management Controller
 */

namespace Admin\Controller;

use Olcs\Controller\CrudAbstract;
use Common\Exception\ResourceNotFoundException;
use Common\Exception\DataServiceException;

/**
 * User Management Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class UserManagementController extends CrudAbstract
{
    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'userId';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'admin-user-management';

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
    protected $formName = 'user';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'admin-user-management';

    protected $pageLayoutInner = null;

    protected $defaultTableSortField = 'id';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'User';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
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
            'contactDetails' => [
                'children' => [
                    'person'
                ]
            ],
            'userRoles' => [
                'children' => [
                    'role'
                ]
            ]
        ]
    );

    /**
     * Entity display name (used by confirm plugin via deleteActionTrait)
     * @var string
     */
    protected $entityDisplayName = 'Publication';


    public function indexAction()
    {
        $this->getViewHelperManager()->get('placeholder')->getContainer('pageTitle')->append('User management');

        return parent::indexAction();

        $view = $this->getView();
        $view->setTemplate('user-management/index');
        return $view;

    }

    /**
     * Redirect action
     *
     * @return \Zend\Http\Response
     */
    public function redirectAction()
    {
        return $this->redirectToRouteAjax(
            'admin-dashboard/admin-publication/pending',
            ['action'=>'index', $this->getIdentifierName() => null],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }

    /**
     * Gets the User service
     *
     * @return mixed
     */
    private function getUserManagementService()
    {
        return $this->getServiceLocator()->get('DataServiceManager')->get('Common\Service\Data\UserManagement');
    }
}
