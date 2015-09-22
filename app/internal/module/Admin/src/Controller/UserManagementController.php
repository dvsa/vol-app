<?php
/**
 * User Management Controller
 */

namespace Admin\Controller;

use Olcs\Controller\CrudAbstract;
use Common\Service\Data\Search\Search;
use Common\Service\Data\Search\SearchType;
use Zend\View\Model\ViewModel;
use Common\Exception\ResourceNotFoundException;
use Common\Exception\BadRequestException;

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
            'roles'
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

        $view->setTemplate('layout/admin-search-results');

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

        $request = $this->getRequest();
        $post = array();

        if ($request->isPost()) {
            $post = (array)$request->getPost();

            if ($post['userType']['userType'] == 'transport-manager') {
                $form = $this->processApplicationTransportManagerLookup($form);
            }
        }

        return $form;
    }

    /**
     * Presentation logic to process an application look up
     *
     * @param $form
     * @return \Zend\Form\Form
     */
    protected function processApplicationTransportManagerLookup($form)
    {
        $request = $this->getRequest();
        $post = array();
        if ($request->isPost()) {
            $post = (array)$request->getPost();
        }

        // If we have clicked find application, persist the form
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
                ->get('application')
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
        $data['attempts'] = 0;

        if (isset($data['id'])) {
            $userService = $this->getUserBusinessService();

            $data['userLoginSecurity']['loginId'] = $data['loginId'];
            $data['userLoginSecurity']['memorableWord'] = $data['memorableWord'];
            $data['userLoginSecurity']['mustResetPassword'] = $data['mustResetPassword'];
            $data['userLoginSecurity']['accountDisabled'] = $data['accountDisabled'];
            $data['userLoginSecurity']['lockedDate'] = $data['lockedDate'];
            $data['userType']['userType'] = $userService->determineUserType($data);
            $data['userType']['team'] = $data['team'];

            if (isset($data['transportManager']['id'])) {
                $data['userType']['transportManager'] = $data['transportManager']['id'];
            }
            if (isset($data['localAuthority']['id'])) {
                $data['userType']['localAuthority'] = $data['localAuthority']['id'];
            }
            if (isset($data['partnerContactDetails']['id'])) {
                $data['userType']['partnerContactDetails'] = $data['partnerContactDetails']['id'];
            }

            $data['userType']['roles'] = [];

            if (isset($data['roles'])) {
                $data['userType']['roles'] = array_column($data['roles'], 'id');
            }

            // set up contact data
            $data['userPersonal']['forename'] = $data['contactDetails']['person']['forename'];
            $data['userPersonal']['familyName'] = $data['contactDetails']['person']['familyName'];
            $data['userPersonal']['birthDate'] = $data['contactDetails']['person']['birthDate'];
            $data['userContactDetails']['emailAddress'] = $data['contactDetails']['emailAddress'];
            $data['userContactDetails']['emailConfirm'] = $data['contactDetails']['emailAddress'];

            if (isset($data['contactDetails']['phoneContacts'])) {
                foreach ($data['contactDetails']['phoneContacts'] as $phoneContact) {
                    if (empty($phoneContact['phoneContactType'])) {
                        continue;
                    }

                    if ($phoneContact['phoneContactType']['id'] == 'phone_t_tel') {
                        $data['userContactDetails']['phone'] = $phoneContact['phoneNumber'];
                    } elseif ($phoneContact['phoneContactType']['id'] == 'phone_t_fax') {
                        $data['userContactDetails']['fax'] = $phoneContact['phoneNumber'];
                    }
                }
            }
            $data['address'] = $data['contactDetails']['address'];

            if (isset($data['lastSuccessfulLoginDate'])) {
                $data['userLoginSecurity']['lastSuccessfulLogin'] = date(
                    'd/m/Y H:i:s',
                    strtotime($data['lastSuccessfulLoginDate'])
                );
            }

            $data['userLoginSecurity']['attempts'] = $data['attempts'];

            if (isset($data['lockedDate'])) {
                $data['userLoginSecurity']['lockedDate'] = date(
                    'd/m/Y H:i:s',
                    strtotime($data['lockedDate'])
                );
            }

            if (isset($data['resetPasswordExpiryDate'])) {
                $data['userLoginSecurity']['resetPasswordExpiryDate'] = date(
                    'd/m/Y H:i:s',
                    strtotime($data['resetPasswordExpiryDate'])
                );
            }
        }

        return $data;
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
            $response = $this->getUserBusinessService()->process($data);
            if ($response->isOk()) {
                $this->addSuccessMessage('User updated successfully');
                $this->setIsSaved(true);
            } else {
                $responseData = $response->getData();
                $this->addErrorMessage($responseData['error']);
            }
        } catch (BadRequestException $e) {
            $this->addErrorMessage($e->getMessage());
        } catch (ResourceNotFoundException $e) {
            $this->addErrorMessage($e->getMessage());
        }

        return $this->redirectToIndex();
    }

    /**
     * Gets the user business service
     *
     * @return mixed
     */
    private function getUserBusinessService()
    {
        return $this->getServiceLocator()->get('BusinessServiceManager')->get('Admin\User');
    }

    /**
    * Gets the transportManagerApplication data service
    *
    * @return mixed
    */
    private function getTransportManagerApplicationService()
    {
        return $this->getServiceLocator()->get('DataServiceManager')
            ->get('Common\Service\Data\TransportManagerApplication');
    }
}
