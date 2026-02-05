<?php

namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Command\Irfo\PrintIrfoPsvAuthChecklist as PrintChecklistDto;
use Dvsa\Olcs\Transfer\Command\Irfo\RenewIrfoPsvAuth as RenewDto;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoPsvAuthContinuationList as ListDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;

class IrfoPsvAuthContinuationController extends AbstractInternalController implements LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-continuation/irfo-psv-auth';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $defaultTableSortField = 'expiryDate';
    protected $defaultTableOrderField = 'ASC';
    protected $tableName = 'admin-irfo-psv-auth-continuation';
    protected $listDto = ListDto::class;
    protected $listVars = ['year', 'month'];

    protected $crudConfig = [
        'renew' => ['requireRows' => true],
        'print' => ['requireRows' => true],
    ];

    /**
     * Gets left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/continuations-irfo',
                'navigationTitle' => 'admin-continuations-title'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Sets the page title
     *
     * @return void
     */
    private function setPageTitle()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'admin-generate-continuation-details-title');
    }

    /**
     * Index action
     *
     * @return ViewModel
     */
    #[\Override]
    public function indexAction()
    {
        $this->setPageTitle();

        return parent::indexAction();
    }

    /**
     * Renew action
     *
     * @return mixed
     */
    public function renewAction()
    {
        return $this->processCommand(
            new AddFormDefaultData(['ids' => explode(',', (string) $this->params()->fromRoute('id'))]),
            RenewDto::class
        );
    }

    /**
     * Print checklist action
     *
     * @return mixed
     */
    public function printAction()
    {
        return $this->processCommand(
            new AddFormDefaultData(['ids' => explode(',', (string) $this->params()->fromRoute('id'))]),
            PrintChecklistDto::class,
            'Checklist printed'
        );
    }
}
