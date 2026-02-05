<?php

namespace Olcs\Controller\Operator;

use Common\RefData;
use Dvsa\Olcs\Transfer\Command\User\DeleteUserSelfserve;
use Dvsa\Olcs\Transfer\Query\User\User;
use Dvsa\Olcs\Transfer\Query\User\UserList as ListDTO;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\OperatorControllerInterface;

class OperatorUsersController extends AbstractInternalController implements
    OperatorControllerInterface,
    LeftViewProvider
{
    protected $tableName = 'operator-users';

    protected $listDto = ListDTO::class;
    protected $listVars = ['organisation'];

    protected $deleteCommand = DeleteUserSelfserve::class;

    protected $inlineScripts = [
        'indexAction' => ['table-actions']
    ];

    /**
     * Get Left View
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/operator/partials/left');
        return $view;
    }

    #[\Override]
    public function deleteAction()
    {
        //we need to check the user isn't the last operator admin
        $userQuery = User::create(['id' => $this->params('id')]);
        $response = $this->handleQuery($userQuery);

        if ($response->isOk()) {
            $user = $response->getResult();
            if ($user['isLastOperatorAdmin'] === true) {
                $this->deleteModalTitle = 'Delete the last admin user?';
                $this->deleteConfirmMessage = 'This is the last operator admin user. There must always be an operator admin account. Are you sure you want to delete this user?';
            }
        } elseif ($response->isClientError() || $response->isServerError()) {
            $this->handleErrors($response->getResult());
        }

        return parent::deleteAction();
    }

    /**
     * Extra parameters
     *
     * @param array $parameters parameters
     *
     * @return array
     */
    #[\Override]
    protected function modifyListQueryParameters($parameters)
    {
        $parameters['roles'] = [
            RefData::ROLE_OPERATOR_TC,
            RefData::ROLE_OPERATOR_ADMIN,
            RefData::ROLE_OPERATOR_TM,
            RefData::ROLE_OPERATOR_USER
        ];

        return $parameters;
    }
}
