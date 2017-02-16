<?php

namespace Admin\Controller;

use Admin\Form\Model\Form\PrinterException as PrinterExceptionForm;
use Admin\Form\Model\Form\Team as TeamForm;
use Common\Category;
use Common\Controller\Traits\GenericRenderView;
use Dvsa\Olcs\Transfer\Command\Team\CreateTeam as CreateDto;
use Dvsa\Olcs\Transfer\Command\Team\DeleteTeam as DeleteDto;
use Dvsa\Olcs\Transfer\Command\Team\UpdateTeam as UpdateDto;
use Dvsa\Olcs\Transfer\Command\TeamPrinter\CreateTeamPrinter as CreateTeamPrinterDto;
use Dvsa\Olcs\Transfer\Command\TeamPrinter\DeleteTeamPrinter as DeleteTeamPrinterDto;
use Dvsa\Olcs\Transfer\Command\TeamPrinter\UpdateTeamPrinter as UpdateTeamPrinterDto;
use Dvsa\Olcs\Transfer\Query\Team\Team as ItemDto;
use Dvsa\Olcs\Transfer\Query\Team\TeamList as ListDto;
use Dvsa\Olcs\Transfer\Query\TeamPrinter\TeamPrinter as TeamPrinterItemDto;
use Dvsa\Olcs\Transfer\Query\TeamPrinter\TeamPrinterExceptionsList as TeamPrinterExceptionsListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\PrinterException as PrinterExceptionMapper;
use Olcs\Data\Mapper\Team as TeamMapper;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;
use Olcs\Mvc\Controller\ParameterProvider\ConfirmItem;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Zend\View\Model\ViewModel;

/**
 * Team management controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TeamController extends AbstractInternalController implements LeftViewProvider
{
    use GenericRenderView;

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-team-management';

    // list
    protected $tableName = 'admin-teams';
    protected $defaultTableSortField = 'name';
    protected $defaultTableOrderField = 'ASC';
    protected $listDto = ListDto::class;
    protected $tableViewTemplate = 'pages/table';

    // add/edit
    protected $itemDto = ItemDto::class;
    protected $itemParams = ['id' => 'team'];
    protected $formClass = TeamForm::class;
    protected $addFormClass = TeamForm::class;
    protected $mapperClass = TeamMapper::class;
    protected $createCommand = CreateDto::class;
    protected $updateCommand = UpdateDto::class;
    protected $routeIdentifier = 'team';

    // delete
    protected $deleteParams = ['id' => 'team'];
    protected $deleteCommand = DeleteDto::class;
    protected $hasMultiDelete = false;
    protected $deleteModalTitle = 'Remove team';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this team?';
    protected $deleteSuccessMessage = 'The team is removed';

    protected $addContentTitle = 'Add team';
    protected $editContentTitle = 'Edit team';

    protected $inlineScripts = [
        'editAction' => ['table-actions'],
        'addRuleAction' => ['forms/printer-exception'],
        'editRuleAction' => ['forms/printer-exception']
    ];

    protected $redirectConfig = [
        'addrule' => [
            'action' => 'edit',
            'routeMap' => [
                'team' => 'team'
            ]
        ],
        'editrule' => [
            'action' => 'edit',
            'routeMap' => [
                'team' => 'team'
            ],
            'reUseParams' => false
        ],
        'deleterule' => [
            'action' => 'edit',
            'routeMap' => [
                'team' => 'team'
            ],
            'reUseParams' => false
        ],
        'edit' => [
            'action' => 'index',
            'reUseParams' => false
        ]
    ];

    /**
     * Get left view
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
     * @return \Olcs\View\Model\ViewModel
     */
    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Teams');

        return parent::indexAction();
    }

    /**
     * Set navigation id
     *
     * @param int $id Id
     *
     * @return void
     */
    protected function setNavigationId($id)
    {
        $this->getServiceLocator()->get('viewHelperManager')->get('placeholder')
            ->getContainer('navigationId')->set($id);
    }

    /**
     * Delete action
     *
     * @return array|mixed|\Zend\Http\Response|ViewModel
     */
    public function deleteAction()
    {
        // validate if we can remove the team
        /** @var DeleteDto $deleteCommand */
        $deleteCommand = $this->deleteCommand;
        $params = $this->prepareParams(['validate' => true]);
        $response = $this->handleCommand($deleteCommand::create($params));
        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        $result = $response->getResult();
        // can't remove the team - display error messages
        if (isset($result['messages']) && $response->isClientError()) {
            $translator = $this->getServiceLocator()->get('translator');
            $messages = array_merge(
                [$translator->translate('internal.admin.reassing-tasks.main-message')],
                $result['messages']
            );
            $message = implode('<br />', $messages);
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($message);
        } elseif ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addUnknownError();
        }

        // it's possible to remove the team, now need to confirm it
        if ($response->isOk()) {
            if (isset($result['id']['tasks'])) {
                // display reassign form, if we have a tasks, assigned to the team
                return $this->processRemoveTeamForm($result['id']['tasks']);
            } else {
                // display standard confirm delete modal, no tasks assigned
                return $this->confirmCommand(
                    new ConfirmItem($this->deleteParams, $this->hasMultiDelete),
                    $this->deleteCommand,
                    $this->deleteModalTitle,
                    $this->deleteConfirmMessage,
                    $this->deleteSuccessMessage
                );
            }
        }
        return $this->redirectTo($response->getResult());
    }

    /**
     * Process remove team form
     *
     * @param int $noOfTasks No of tasks
     *
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
     */
    protected function processRemoveTeamForm($noOfTasks)
    {
        $form = $this->alterTeamsForm($this->getForm('TeamRemove'));
        if ($this->getRequest()->isPost()) {
            $post = $this->params()->fromPost();
            $form->setData($post);

            if ($form->isValid()) {
                /** @var DeleteDto $deleteCommand */
                $deleteCommand = $this->deleteCommand;
                $response = $this->handleCommand(
                    $deleteCommand::create($this->prepareParams($post))
                );
                if ($response->isClientError() || $response->isServerError()) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
                }
                if ($response->isOk()) {
                    $this->getServiceLocator()
                        ->get('Helper\FlashMessenger')->addSuccessMessage($this->deleteSuccessMessage);
                }
                return $this->redirectTo($response->getResult());
            }
        }
        return $this->renderView($form, $noOfTasks);
    }

    /**
     * Prepare params
     *
     * @param array $defaultParams Default params
     *
     * @return array
     */
    protected function prepareParams($defaultParams = [])
    {
        $paramProvider = new ConfirmItem($this->deleteParams, $this->hasMultiDelete);
        $paramProvider->setParams($this->plugin('params'));
        $params = $paramProvider->provideParameters();
        if (isset($defaultParams['team-remove-details']['newTeam'])) {
            $params['newTeam'] = $defaultParams['team-remove-details']['newTeam'];
        }
        if (isset($defaultParams['validate'])) {
            $params['validate'] = $defaultParams['validate'];
        }
        return $params;
    }

    /**
     * Render view
     *
     * @param \Zend\Form\Form $form      Form
     * @param int             $noOfTasks No of tasks
     *
     * @return \Zend\View\Model\ViewModel
     */
    protected function renderView($form, $noOfTasks)
    {
        $view = new ViewModel();
        $view->setVariable('form', $form);
        $view->setVariable(
            'label',
            $this->getServiceLocator()->get('Helper\Translation')
                ->translateReplace('internal.admin.remove-team-label', [$noOfTasks])
        );
        $view->setTemplate('pages/confirm');
        $this->placeholder()->setPlaceholder('pageTitle', $this->deleteModalTitle);
        return $this->viewBuilder()->buildView($view);
    }

    /**
     * Alter teams form
     *
     * @param \Zend\Form\Form $form Form
     *
     * @return \Zend\Form\Form
     */
    protected function alterTeamsForm($form)
    {
        $valueOptions = $form->get('team-remove-details')->get('newTeam')->getValueOptions();
        // remove the current team from the list, we shouldn't reassign tasks to the same team
        unset($valueOptions[$this->params()->fromRoute('team')]);
        $form->get('team-remove-details')->get('newTeam')->setValueOptions($valueOptions);
        return $form;
    }

    /**
     * Alter form for add action - remove the printers exceptions table
     *
     * @param \Zend\Form\Form $form Form
     *
     * @return \Zend\Form\Form
     */
    protected function alterFormForAdd($form)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $formHelper->remove($form, 'team-details->printerExceptions');
        return $form;
    }

    /**
     * Alter form for editRule action, set default values for listboxes
     *
     * @param \Zend\Form\Form $form     Form
     * @param array           $formData Form data
     *
     * @return \Zend\Form\Form
     */
    protected function alterFormForEditRule($form, $formData)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $formHelper->remove($form, 'form-actions->addAnother');

        $defaultCategory = isset($formData['team-printer']['categoryTeam']) ?
            $formData['team-printer']['categoryTeam'] : Category::CATEGORY_APPLICATION;

        $this->getServiceLocator()->get(\Olcs\Service\Data\SubCategory::class)
            ->setCategory($defaultCategory);

        $defaultTeam = isset($formData['exception-details']['team']) ?
            $formData['exception-details']['team'] : $this->params()->fromRoute('team', null);

        $this->getServiceLocator()->get('Olcs\Service\Data\UserWithName')
            ->setTeam($defaultTeam);

        return $form;
    }

    /**
     * Alter form for addRule action, set default values for listboxes
     *
     * @param \Zend\Form\Form $form     Form
     * @param array           $formData Form data
     *
     * @return \Zend\Form\Form
     */
    protected function alterFormForAddRule($form, $formData)
    {
        $this->getServiceLocator()->get(\Olcs\Service\Data\SubCategory::class)
            ->setCategory(Category::CATEGORY_APPLICATION);

        $defaultTeam = isset($formData['exception-details']['team']) ?
            $formData['exception-details']['team'] : $this->params()->fromRoute('team', null);
        $this->getServiceLocator()->get('Olcs\Service\Data\UserWithName')
            ->setTeam($defaultTeam);

        return $form;
    }

    /**
     * Overwrite getForm method to inject the table
     *
     * @param string $name Name
     *
     * @return \Zend\Form\Form
     */
    public function getForm($name)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper->createForm($name);
        $formHelper->setFormActionFromRequest($form, $this->getRequest());
        if ($name === 'Admin\Form\Model\Form\Team') {
            $formHelper->populateFormTable(
                $form->get('team-details')->get('printerExceptions'), $this->getExceptionsTable()
            );
        } elseif ($name === 'Admin\Form\Model\Form\PrinterException') {
            $teamId = $this->params()->fromRoute('team');
            $this->getServiceLocator()->get('Olcs\Service\Data\UserWithName')->setTeam($teamId);
        }

        return $form;
    }

    /**
     * Get printer exceptions table
     *
     * @return \Common\Service\Table\TableBuilder
     */
    protected function getExceptionsTable()
    {
        return $this->getServiceLocator()->get('Table')
            ->prepareTable('admin-printers-exceptions', $this->getTableData());
    }

    /**
     * Get table data
     *
     * @return array
     */
    protected function getTableData()
    {
        if (empty($this->params()->fromRoute('team'))) {
            return [];
        }

        $data = [
            'team' => $this->params()->fromRoute('team'),
        ];
        $response = $this->handleQuery(TeamPrinterExceptionsListDto::create($data));

        if ($response->isServerError() || $response->isClientError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            return $response->getResult();
        }

        return [];
    }

    /**
     * Edit action
     *
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
     */
    public function editAction()
    {
        $params = [];

        $query = $this->params()->fromQuery();
        $tableAction = null;
        if (isset($query['table']['action'])) {
            $tableAction = $query['table']['action'];
            $params = [
                'action' => $tableAction,
                'rule' => $query['id']
            ];
            if (isset($query['team-details']['id'])) {
                $params['team'] = $query['team-details']['id'];
            }
        }
        if (!$tableAction) {
            return $this->edit(
                $this->formClass,
                $this->itemDto,
                new GenericItem($this->itemParams),
                $this->updateCommand,
                $this->mapperClass,
                $this->editViewTemplate,
                $this->editSuccessMessage,
                $this->editContentTitle
            );
        }
        return $this->redirect()->toRoute(null, $params, ['code' => 303], true);
    }

    /**
     * Add rule action
     *
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
     */
    public function addRuleAction()
    {
        return $this->add(
            PrinterExceptionForm::class,
            new AddFormDefaultData(['team' => 'route']),
            CreateTeamPrinterDto::class,
            PrinterExceptionMapper::class,
            $this->editViewTemplate,
            'Printer exception added',
            'Add printer exception'
        );
    }

    /**
     * Edit rule action
     *
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
     */
    public function editRuleAction()
    {
        return $this->edit(
            PrinterExceptionForm::class,
            TeamPrinterItemDto::class,
            new GenericItem(['id' => 'rule']),
            UpdateTeamPrinterDto::class,
            PrinterExceptionMapper::class,
            $this->editViewTemplate,
            'Printer exception updated',
            'Edit printer exception'
        );
    }

    /**
     * Delete rule action
     *
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
     */
    public function deleteRuleAction()
    {
        return $this->confirmCommand(
            new ConfirmItem(['id' => 'rule']),
            DeleteTeamPrinterDto::class,
            'Delete printer exception',
            'Are you sure you want to remove this printer exception?',
            'Printer exceptions removed'
        );
    }
}
