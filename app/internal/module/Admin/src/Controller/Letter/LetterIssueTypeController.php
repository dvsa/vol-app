<?php

declare(strict_types=1);

namespace Admin\Controller\Letter;

use Admin\Data\Mapper\Letter\LetterIssueType as LetterIssueTypeMapper;
use Admin\Form\Model\Form\Letter\LetterIssueType as LetterIssueTypeForm;
use Dvsa\Olcs\Transfer\Command\Letter\LetterIssueType\Create as CreateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterIssueType\Update as UpdateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterIssueType\Delete as DeleteDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterIssueType\Get as ItemDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterIssueType\GetList as ListDTO;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class LetterIssueTypeController extends AbstractInternalController implements LeftViewProvider
{
    protected $tableName = 'admin-letter-issue-type';
    protected $defaultTableSortField = 'displayOrder';
    protected $defaultTableOrderField = 'ASC';

    protected $listDto = ListDTO::class;
    protected $itemDto = ItemDTO::class;
    protected $itemParams = ['id'];

    protected $formClass = LetterIssueTypeForm::class;
    protected $addFormClass = LetterIssueTypeForm::class;
    protected $mapperClass = LetterIssueTypeMapper::class;

    protected $createCommand = CreateDTO::class;
    protected $updateCommand = UpdateDTO::class;
    protected $deleteCommand = DeleteDTO::class;

    protected $addContentTitle = 'Add Letter Issue Type';
    protected $editContentTitle = 'Edit Letter Issue Type';

    protected $deleteModalTitle = 'Remove Letter Issue Type';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this letter issue type?';
    protected $deleteSuccessMessage = 'The letter issue type has been removed';

    protected $addSuccessMessage = 'Letter issue type created successfully';
    protected $editSuccessMessage = 'Letter issue type updated successfully';

    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
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
