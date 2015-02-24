<?php

/**
 * Financial Standing Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Admin\Controller;

use Common\Controller\Interfaces\CrudControllerInterface;

/**
 * Financial Standing Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FinancialStandingController extends AbstractActionController implements CrudControllerInterface
{
    public function indexAction()
    {
        $crudService = $this->getServiceLocator()->get('Crud\FinancialStanding');

        return $this->renderTable($crudService->getList(), 'Financial standing rates');
    }

    public function addAction()
    {
        die('foo');
    }
}
