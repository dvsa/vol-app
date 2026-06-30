<?php

declare(strict_types=1);

namespace Admin\Controller\Letter;

use Admin\Data\Mapper\Letter\LetterTodo as LetterTodoMapper;
use Admin\Form\Model\Form\Letter\LetterTodo as LetterTodoForm;
use Dvsa\Olcs\Transfer\Command\Letter\LetterTodo\Create as CreateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterTodo\Update as UpdateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterTodo\Delete as DeleteDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterTodo\Get as ItemDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterTodo\GetList as ListDTO;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class LetterTodoController extends AbstractInternalController implements LeftViewProvider
{
    protected $tableName = 'admin-letter-todo';
    protected $defaultTableSortField = 'todoKey';
    protected $defaultTableOrderField = 'ASC';

    protected $listDto = ListDTO::class;
    protected $itemDto = ItemDTO::class;
    protected $itemParams = ['id'];

    protected $formClass = LetterTodoForm::class;
    protected $addFormClass = LetterTodoForm::class;
    protected $mapperClass = LetterTodoMapper::class;

    protected $createCommand = CreateDTO::class;
    protected $updateCommand = UpdateDTO::class;
    protected $deleteCommand = DeleteDTO::class;

    protected $addContentTitle = 'Add Letter Todo';
    protected $editContentTitle = 'Edit Letter Todo (Creates New Version)';

    protected $deleteModalTitle = 'Remove Letter Todo';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this letter todo?';
    protected $deleteSuccessMessage = 'The letter todo has been removed';

    protected $addSuccessMessage = 'Letter todo created successfully';
    protected $editSuccessMessage = 'Letter todo updated successfully (new version created)';

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
