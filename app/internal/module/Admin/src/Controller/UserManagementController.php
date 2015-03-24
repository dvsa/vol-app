<?php
/**
 * User Management Controller
 */

namespace Admin\Controller;

use Olcs\Controller\CrudAbstract;

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
    protected $identifierName = 'user';

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
    protected $pageLayout = 'admin-layout';

    protected $defaultTableSortField = 'id';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'User';

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
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-user-management';

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
     * @var array
     */
    //protected $inlineScripts = ['table-actions'];

    /**
     * Entity display name (used by confirm plugin via deleteActionTrait)
     * @var string
     */
    protected $entityDisplayName = 'Users';

    public function indexAction()
    {
        $this->getViewHelperManager()
            ->get('placeholder')
            ->getContainer('navigationId')
            ->set($this->navigationId);
        return parent::indexAction();
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
