<?php

declare(strict_types=1);

namespace Admin\Controller\Letter;

use Admin\Data\Mapper\Letter\LetterChoice as LetterChoiceMapper;
use Admin\Form\Model\Form\Letter\LetterChoice as LetterChoiceForm;
use Dvsa\Olcs\Transfer\Command\Letter\LetterChoice\Create as CreateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterChoice\Update as UpdateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterChoice\Delete as DeleteDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterChoice\Get as ItemDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterChoice\GetList as ListDTO;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class LetterChoiceController extends AbstractInternalController implements LeftViewProvider
{
    protected $tableName = 'admin-letter-choice';
    protected $defaultTableSortField = 'choiceKey';
    protected $defaultTableOrderField = 'ASC';

    protected $listDto = ListDTO::class;
    protected $itemDto = ItemDTO::class;
    protected $itemParams = ['id'];

    protected $formClass = LetterChoiceForm::class;
    protected $addFormClass = LetterChoiceForm::class;
    protected $mapperClass = LetterChoiceMapper::class;

    protected $createCommand = CreateDTO::class;
    protected $updateCommand = UpdateDTO::class;
    protected $deleteCommand = DeleteDTO::class;

    protected $addContentTitle = 'Add Letter Choice';
    protected $editContentTitle = 'Edit Letter Choice';

    protected $deleteModalTitle = 'Remove Letter Choice';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this letter choice?';
    protected $deleteSuccessMessage = 'The letter choice has been removed';

    protected $addSuccessMessage = 'Letter choice created successfully';
    protected $editSuccessMessage = 'Letter choice updated successfully';

    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

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
