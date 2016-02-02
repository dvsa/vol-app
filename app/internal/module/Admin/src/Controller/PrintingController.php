<?php

/**
 * Printing Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Admin\Controller;

use Olcs\Controller\AbstractInternalController;
use Common\Controller\Traits\GenericMethods;
use Dvsa\Olcs\Transfer\Command\Printer\CreatePrinter as CreateDto;
use Dvsa\Olcs\Transfer\Command\Printer\UpdatePrinter as UpdateDto;
use Dvsa\Olcs\Transfer\Command\Printer\DeletePrinter as DeleteDto;
use Dvsa\Olcs\Transfer\Query\Printer\Printer as ItemDto;
use Dvsa\Olcs\Transfer\Query\Printer\PrinterList as ListDto;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\Printer as PrinterMapper;
use Admin\Form\Model\Form\Printer as PrinterForm;
use Zend\View\Model\ViewModel;

/**
 * Printing Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PrintingController extends AbstractInternalController implements LeftViewProvider
{
    use GenericMethods;

    protected $navigationId = 'admin-dashboard/admin-printing/admin-printer-management';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    // list
    protected $tableName = 'admin-printers';
    protected $defaultTableSortField = 'printerName';
    protected $defaultTableOrderField = 'ASC';
    protected $listDto = ListDto::class;

    // add/edit
    protected $itemDto = ItemDto::class;
    protected $itemParams = ['id' => 'team'];
    protected $formClass = PrinterForm::class;
    protected $addFormClass = PrinterForm::class;
    protected $mapperClass = PrinterMapper::class;
    protected $createCommand = CreateDto::class;
    protected $updateCommand = UpdateDto::class;
    protected $routeIdentifier = 'printer';

    // delete
    protected $deleteParams = ['id' => 'printer'];
    protected $deleteCommand = DeleteDto::class;
    protected $hasMultiDelete = false;
    protected $deleteModalTitle = 'Remove printer';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this printer?';
    protected $deleteSuccessMessage = 'The printer is removed';

    protected $addContentTitle = 'Add printer';
    protected $editContentTitle = 'Edit printer';

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Printers');

        return parent::indexAction();
    }

    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-printing',
                'navigationTitle' => 'Printing'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    protected function setNavigationId($id)
    {
        $this->getServiceLocator()->get('viewHelperManager')->get('placeholder')
            ->getContainer('navigationId')->set($id);
    }

    protected function alterFormForEdit($form)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $formHelper->remove($form, 'form-actions->addAnother');
        return $form;
    }
}
