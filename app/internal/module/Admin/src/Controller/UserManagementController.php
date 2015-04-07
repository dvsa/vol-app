<?php
/**
 * User Management Controller
 */

namespace Admin\Controller;

use Olcs\Controller\CrudAbstract;
use Common\Service\Data\Search\Search;
use Olcs\Service\Data\Search\SearchType;
use Zend\View\Model\ViewModel;

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
     * Query Elastic for list of users
     *
     * @return array|mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $data['search'] = '*';

        $this->checkForCrudAction(null, [], $this->getIdentifierName());

        //update data with information from route, and rebind to form so that form data is correct
        $data['index'] = 'user';

        /** @var Search $searchService **/
        $searchService = $this->getServiceLocator()->get('DataServiceManager')->get(Search::class);

        $searchService->setQuery($this->getRequest()->getQuery())
            ->setRequest($this->getRequest())
            ->setIndex($data['index'])
            ->setSearch($data['search']);

        $view = new ViewModel();

        $view->results = $searchService->fetchResultsTable();

        $view->setTemplate('layout/search-results');

        return $this->renderView($view, 'User management');
    }

    /**
     * Gets a from from either a built or custom form config.
     * @param type $type
     * @return type
     */
    public function getForm($type)
    {
        $form = parent::getForm($type);

        $form = $this->processApplicationTransportManagerLookup($form);

        return $form;
    }

    protected function processApplicationTransportManagerLookup($form)
    {
        $request = $this->getRequest();
        $post = array();
        if ($request->isPost()) {
            $post = (array)$request->getPost();
        }

        // If we haven't posted a form, or we haven't clicked find application
        if (isset($post['userType']['applicationTransportManagers']['search'])
            && !empty($post['userType']['applicationTransportManagers']['search'])) {
            $this->persist = false;
        }

        if (isset($post['userType']['applicationTransportManagers']['application'])) {
            $applicationId = trim($post['userType']['applicationTransportManagers']['application']);
        }

        if (empty($applicationId) || !is_numeric($applicationId)) {
            $form->get('userType')
                ->get('applicationTransportManagers')
                ->setMessages(array('Please enter a valid application number'));
        } else {
            $tmList = $this->getTransportManagerApplicationService()->fetchTmListOptionsByApplicationId($applicationId);
            if (empty($tmList)) {
                $form->get('userType')
                    ->get('applicationTransportManagers')
                    ->get('application')
                    ->setMessages(array('No transport managers found for application'));
            } else {
                $form->get('userType')
                    ->get('transportManager')
                    ->setValueOptions($tmList);
            }
        }

        return $form;
    }

    /**
     * Call formatLoad to prepare backend data for form view
     *
     * @param array $data
     * @return array
     */
    public function processLoad($data)
    {
        $data['attempts'] = empty($data['attempts']) ? '0' :
            $data['attempts'];

        if (isset($data['id'])) {
            $data =  $this->getUserService()->formatDataForUserRoleForm($data);
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

    /**
 * Gets the transport manager service
 *
 * @return mixed
 */
    private function getTransportManagerApplicationService()
    {
        return $this->getServiceLocator()->get('DataServiceManager')->get
            ('Common\Service\Data\TransportManagerApplication');
    }
}
