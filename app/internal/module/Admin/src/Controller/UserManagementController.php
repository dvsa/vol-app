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

    protected $pageLayoutInner = null;

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
            'hintQuestion1',
            'hintQuestion2',
            'team',
            'transportManager',
            'partnerContactDetails',
            'localAuthority',
            'contactDetails' => [
                'children' => [
                    'address',
                    'person',
                    'phoneContacts' => [
                        'children' => [
                            'phoneContactType'
                        ]
                    ]
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
    protected $inlineScripts = ['table-actions', 'forms/user-type'];

    /**
     * Entity display name (used by confirm plugin via deleteActionTrait)
     * @var string
     */
    protected $entityDisplayName = 'Users';

    /**
     * Call formatLoad to prepare backend data for form view
     *
     * @param array $data
     * @return array
     */
    public function processLoad($data)
    {
        if (isset($data['id'])) {
            return $this->getUserService()->formatDataForUserRoleForm($data);
        }

        return parent::processLoad($data);
    }

    /**
     * Form has passed validation so call the user service to save the record
     *
     * @param array $data
     * @return mixed
     */
    public function processSave($data)
    {
        try {
            $userData = $this->getUserService()->saveUserRole($data);
            $this->addSuccessMessage('User updated successfully');

            $this->setIsSaved(true);

        } catch (BadRequestException $e) {
            $this->addErrorMessage($e->getMessage());
            $id = false;
        } catch (ResourceNotFoundException $e) {
            $this->addErrorMessage($e->getMessage());
            $id = false;
        }

        return $this->redirectToIndex();
    }

    /**
     * Gets the user service
     *
     * @return mixed
     */
    private function getUserService()
    {
        return $this->getServiceLocator()->get('DataServiceManager')->get('Common\Service\Data\User');
    }
}
