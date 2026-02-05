<?php

declare(strict_types=1);

namespace Admin\Controller\Letter;

use Admin\Data\Mapper\Letter\LetterIssue as LetterIssueMapper;
use Admin\Form\Model\Form\Letter\LetterIssue as LetterIssueForm;
use Dvsa\Olcs\Transfer\Command\Letter\LetterIssue\Create as CreateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterIssue\Update as UpdateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterIssue\Delete as DeleteDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterIssue\Get as ItemDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterIssue\GetList as ListDTO;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class LetterIssueController extends AbstractInternalController implements LeftViewProvider
{
    protected $tableName = 'admin-letter-issue';
    protected $defaultTableSortField = 'issueKey';
    protected $defaultTableOrderField = 'ASC';

    protected $listDto = ListDTO::class;
    protected $itemDto = ItemDTO::class;
    protected $itemParams = ['id'];

    protected $formClass = LetterIssueForm::class;
    protected $addFormClass = LetterIssueForm::class;
    protected $mapperClass = LetterIssueMapper::class;

    protected $createCommand = CreateDTO::class;
    protected $updateCommand = UpdateDTO::class;
    protected $deleteCommand = DeleteDTO::class;

    protected $addContentTitle = 'Add Letter Issue';
    protected $editContentTitle = 'Edit Letter Issue (Creates New Version)';

    protected $deleteModalTitle = 'Remove Letter Issue';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this letter issue?';
    protected $deleteSuccessMessage = 'The letter issue has been removed';

    protected $addSuccessMessage = 'Letter issue created successfully';
    protected $editSuccessMessage = 'Letter issue updated successfully (new version created)';

    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
        'addAction' => ['forms/letter-issue'],
        'editAction' => ['forms/letter-issue'],
    ];

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
