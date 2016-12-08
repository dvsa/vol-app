<?php
/**
 * User Management Controller
 */

namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Command\User\CreateUser as CreateDto;
use Dvsa\Olcs\Transfer\Command\User\UpdateUser as UpdateDto;
use Dvsa\Olcs\Transfer\Command\User\DeleteUser as DeleteDto;
use Dvsa\Olcs\Transfer\Query\User\User as ItemDto;
use Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetList as TransportManagerApplicationListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\User as Mapper;
use Admin\Form\Model\Form\User as Form;
use Zend\View\Model\ViewModel;
use Common\RefData;
use Zend\Form\Fieldset as FormFieldset;
use Zend\Form\Element\Radio as RadioElement;

/**
 * User Management Controller
 *
 * @method redirect Zend\Mvc\Controller\Plugin\Redirect
 */
class UserManagementController extends AbstractInternalController implements LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-user-management';

    /**
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = array(
        'indexAction' => ['table-actions'],
        'addAction' => ['forms/user-type'],
        'editAction' => ['forms/user-type'],
    );

    protected $routeIdentifier = 'user';

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $itemDto = ItemDto::class;
    // 'id' => 'conviction', to => from
    protected $itemParams = ['id' => 'user'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Form::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;
    protected $addContentTitle = 'Add user';
    protected $editContentTitle = 'Edit user';

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $createCommand = CreateDto::class;

    /**
     * Variables for controlling the delete action.
     * Command is required, as are itemParams from above
     */
    protected $deleteCommand = DeleteDto::class;
    protected $deleteParams = ['id' => 'user'];
    protected $deleteModalTitle = 'Delete user';

    /**
     * Allows override of default behaviour for redirects. See Case Overview Controller
     *
     * @var array
     */
    protected $redirectConfig = [
        'add' => [
            'action' => 'index'
        ],
        'edit' => [
            'action' => 'index'
        ],
        'delete' => [
            'action' => 'index'
        ]
    ];

    /**
     * Defines left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-user-management',
                'navigationTitle' => 'User management'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Index action
     *
     * @return \Zend\Http\Response
     */
    public function indexAction()
    {
        return $this->redirect()->toRoute('search', ['index' => 'user', 'action' => 'search'], ['code' => 303]);
    }

    /**
     * Gets a form from either a built or custom form config.
     *
     * @param string $type form type
     *
     * @return \Zend\Form\Form
     */
    public function getForm($type)
    {
        $form = parent::getForm($type);

        $request = $this->getRequest();
        $post = array();

        if ($request->isPost()) {
            $post = (array)$request->getPost();

            if ($post['userType']['userType'] === 'transport-manager') {
                $form = $this->processApplicationTransportManagerLookup($form);
            }
        }

        return $form;
    }

    /**
     * Alters the form for add
     *
     * @param \Zend\Form\Form $form The form to alter
     * @param array           $data Form data
     *
     * @return \Zend\Form\Form
     */
    protected function alterFormForAdd($form, $data)
    {
        $form->get('userType')->remove('currentTransportManagerHtml');
        $form->get('userLoginSecurity')->remove('accountDisabled');
        $form->get('userLoginSecurity')->remove('disabledDate');
        $form->get('userLoginSecurity')->remove('resetPassword');
        $form->get('userLoginSecurity')->remove('createdOn');

        return $form;
    }

    /**
     * Alters the form for edit
     *
     * @param \Zend\Form\Form $form The form to alter
     * @param array           $data Form data
     *
     * @return \Zend\Form\Form
     */
    protected function alterFormForEdit($form, $data)
    {
        /**
         * @var FormFieldset $userLoginSecurity
         * @var RadioElement $resetPwField
         */
        $userLoginSecurity = $form->get('userLoginSecurity');

        if (empty($data['userLoginSecurity']['disabledDate'])) {
            $userLoginSecurity->remove('disabledDate');
        }

        if (!empty($data['userType']['currentTransportManager'])
            && !empty($data['userType']['currentTransportManagerName'])
        ) {
            $value = sprintf(
                '<a href="%s">%s</a>',
                $this->getServiceLocator()->get('Helper\Url')->fromRoute(
                    'transport-manager',
                    ['transportManager' => $data['userType']['currentTransportManager']]
                ),
                $data['userType']['currentTransportManagerName']
            );
            $form->get('userType')->get('currentTransportManagerHtml')->setValue($value);
        } else {
            $form->get('userType')->remove('currentTransportManagerHtml');
        }

        //password reset options
        switch ($data['userType']['userType']) {
            case RefData::USER_TYPE_INTERNAL:
                //no reset option for internal user
                $userLoginSecurity->remove('resetPassword');
                break;
            case RefData::USER_TYPE_PARTNER:
            case RefData::USER_TYPE_LOCAL_AUTHORITY:
                //for partners and local authorities, remove the post option
                $resetPwField = $userLoginSecurity->get('resetPassword');
                $valueOptions = $resetPwField->getValueOptions();
                unset($valueOptions['post']);
                $resetPwField->setValueOptions($valueOptions);
                break;
            default:
                //transport manager and operator, we don't modify the form
        }

        return $form;
    }

    /**
     * Presentation logic to process an application look up
     *
     * @param \Zend\Form\Form $form the form
     *
     * @return \Zend\Form\Form
     */
    protected function processApplicationTransportManagerLookup($form)
    {
        $request = $this->getRequest();

        $post = ($request->isPost()) ? (array)$request->getPost() : [];

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
            $tmList = $this->fetchTmListOptionsByApplicationId($applicationId);

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
     * Fetches a list of Transport Managers by application Id
     *
     * @param int $applicationId application id
     *
     * @return array
     */
    protected function fetchTmListOptionsByApplicationId($applicationId)
    {
        $response = $this->handleQuery(
            TransportManagerApplicationListDto::create(
                [
                    'application' => $applicationId
                ]
            )
        );

        if ($response->isServerError() || $response->isClientError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        $optionData = [];

        if ($response->isOk()) {
            $data = $response->getResult();

            foreach ($data['results'] as $datum) {
                $optionData[$datum['transportManager']['id']]
                    = $datum['transportManager']['homeCd']['person']['forename'] . ' ' .
                        $datum['transportManager']['homeCd']['person']['familyName'];
            }
        }

        return $optionData;
    }
}
