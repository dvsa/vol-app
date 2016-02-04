<?php
/**
 * Team management controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Admin\Controller;

use Common\Controller\Traits\GenericRenderView;
use Dvsa\Olcs\Transfer\Command\Team\CreateTeam as CreateDto;
use Dvsa\Olcs\Transfer\Command\Team\UpdateTeam as UpdateDto;
use Dvsa\Olcs\Transfer\Command\Team\DeleteTeam as DeleteDto;
use Dvsa\Olcs\Transfer\Query\Team\Team as ItemDto;
use Dvsa\Olcs\Transfer\Query\Team\TeamList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\Team as TeamMapper;
use Admin\Form\Model\Form\Team as TeamForm;
use Zend\View\Model\ViewModel;
use Olcs\Mvc\Controller\ParameterProvider\ConfirmItem;

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

    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Teams');

        return parent::indexAction();
    }

    protected function setNavigationId($id)
    {
        $this->getServiceLocator()->get('viewHelperManager')->get('placeholder')
            ->getContainer('navigationId')->set($id);
    }

    public function deleteAction()
    {
        // validate if we can remove the team
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
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
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

    protected function processRemoveTeamForm($noOfTasks)
    {
        $form = $this->alterTeamsForm($this->getForm('TeamRemove'));
        if ($this->getRequest()->isPost()) {
            $post = $this->params()->fromPost();
            $form->setData($post);
            if ($form->isValid()) {
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

    protected function alterTeamsForm($form)
    {
        $valueOptions = $form->get('team-remove-details')->get('newTeam')->getValueOptions();
        // remove the current team from the list, we shouldn't reassign tasks to the same team
        unset($valueOptions[$this->params()->fromRoute('team')]);
        $form->get('team-remove-details')->get('newTeam')->setValueOptions($valueOptions);
        return $form;
    }
}
