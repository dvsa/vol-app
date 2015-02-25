<?php

/**
 * Financial Standing Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Common\Controller\Crud\CrudControllerTrait;
use Common\Controller\Interfaces\CrudControllerInterface;

/**
 * Financial Standing Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FinancialStandingController extends AbstractActionController implements CrudControllerInterface
{
    use CrudControllerTrait;

    public function indexAction()
    {
        $crudService = $this->getServiceLocator()->get('Crud\FinancialStanding');

        $this->getServiceLocator()->get('Script')->loadFile('table-actions');

        return $this->renderTable($crudService->getList(), 'financial-standing-rate-title');
    }

    public function addAction()
    {
        $crudService = $this->getServiceLocator()->get('Crud\FinancialStanding');

        return $this->addOrEditForm($crudService, 'financial-standing-rate-form-add');
    }

    public function editAction()
    {
        $id = $this->params('id', 0);
        $crudService = $this->getServiceLocator()->get('Crud\FinancialStanding');

        return $this->addOrEditForm($crudService, 'financial-standing-rate-form-edit', $id);
    }

    public function deleteAction()
    {
        $id = $this->params('id', 0);
        $crudService = $this->getServiceLocator()->get('Crud\FinancialStanding');

        return $this->confirmDelete(
            $crudService,
            'financial-standing-rate-delete-title',
            'financial-standing-rate-delete-message',
            $id
        );
    }
}
