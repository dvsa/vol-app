<?php

namespace Olcs\Controller\Operator;

use Common\RefData;
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

    /**
     * Extra parameters
     *
     * @param array $parameters parameters
     *
     * @return array
     */
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
