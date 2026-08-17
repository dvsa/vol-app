<?php

declare(strict_types=1);

namespace Admin\Controller\Letter;

use Admin\Data\Mapper\Letter\LetterType as LetterTypeMapper;
use Admin\Form\Model\Form\Letter\LetterType as LetterTypeForm;
use Dvsa\Olcs\Transfer\Command\Letter\LetterType\Create as CreateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterType\Update as UpdateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterType\Delete as DeleteDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterType\Get as ItemDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterType\GetList as ListDTO;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class LetterTypeController extends AbstractInternalController implements LeftViewProvider
{
    protected $tableName = 'admin-letter-type';
    protected $defaultTableSortField = 'name';
    protected $defaultTableOrderField = 'ASC';

    protected $listDto = ListDTO::class;
    protected $itemDto = ItemDTO::class;
    protected $itemParams = ['id'];

    protected $formClass = LetterTypeForm::class;
    protected $addFormClass = LetterTypeForm::class;
    protected $mapperClass = LetterTypeMapper::class;

    protected $createCommand = CreateDTO::class;
    protected $updateCommand = UpdateDTO::class;
    protected $deleteCommand = DeleteDTO::class;

    protected $addContentTitle = 'Add Letter Type';
    protected $editContentTitle = 'Edit Letter Type';

    protected $deleteModalTitle = 'Remove Letter Type';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this letter type?';
    protected $deleteSuccessMessage = 'The letter type has been removed';

    protected $addSuccessMessage = 'Letter type created successfully';
    protected $editSuccessMessage = 'Letter type updated successfully';

    protected $inlineScripts = [
        'indexAction' => ['table-actions', 'forms/letter-type-index'],
        'addAction' => ['forms/letter-type'],
        'editAction' => ['forms/letter-type'],
    ];

    protected $crudConfig = [
        'builder' => ['requireRows' => true],
    ];

    /**
     * Open the selected letter type in the builder.
     *
     * The builder is reached from a row rather than the side menu because it has nothing to show
     * without a letter type -- a menu entry could only ever land on an empty screen. The table
     * marks this action js-require--one, so exactly one row is selected by the time we get here.
     */
    public function builderAction()
    {
        $ids = explode(',', (string) $this->params()->fromRoute('id'));
        $letterTypeId = (int) reset($ids);

        if ($letterTypeId === 0) {
            $this->flashMessengerHelperService->addErrorMessage('Select a letter type to open in the builder');

            return $this->redirect()->toRoute('admin-dashboard/admin-letter-type', ['action' => 'index'], [], false);
        }

        return $this->redirect()->toRoute(
            'admin-dashboard/admin-letter-type-builder',
            ['action' => 'index', 'id' => $letterTypeId],
            [],
            false
        );
    }

    #[\Override]
    public function getLeftView(): ViewModel
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/letter-management',
                'navigationTitle' => 'Letter Management',
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }
}
