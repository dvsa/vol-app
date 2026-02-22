<?php

declare(strict_types=1);

namespace Admin\Controller\Letter;

use Admin\Data\Mapper\Letter\LetterSection as LetterSectionMapper;
use Admin\Form\Model\Form\Letter\LetterSection as LetterSectionForm;
use Dvsa\Olcs\Transfer\Command\Letter\LetterSection\Create as CreateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterSection\Update as UpdateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterSection\Delete as DeleteDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterSection\Get as ItemDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterSection\GetList as ListDTO;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class LetterSectionController extends AbstractInternalController implements LeftViewProvider
{
    protected $tableName = 'admin-letter-section';
    protected $defaultTableSortField = 'sectionKey';
    protected $defaultTableOrderField = 'ASC';

    protected $listDto = ListDTO::class;
    protected $itemDto = ItemDTO::class;
    protected $itemParams = ['id'];

    protected $formClass = LetterSectionForm::class;
    protected $addFormClass = LetterSectionForm::class;
    protected $mapperClass = LetterSectionMapper::class;

    protected $createCommand = CreateDTO::class;
    protected $updateCommand = UpdateDTO::class;
    protected $deleteCommand = DeleteDTO::class;

    protected $addContentTitle = 'Add Letter Section';
    protected $editContentTitle = 'Edit Letter Section (Creates New Version)';

    protected $deleteModalTitle = 'Remove Letter Section';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this letter section?';
    protected $deleteSuccessMessage = 'The letter section has been removed';

    protected $addSuccessMessage = 'Letter section created successfully';
    protected $editSuccessMessage = 'Letter section updated successfully (new version created)';

    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
        'addAction' => ['forms/letter-section'],
        'editAction' => ['forms/letter-section'],
    ];

    /**
     * Version history action - display all versions of a section
     */
    public function versionHistoryAction()
    {
        $id = $this->params()->fromRoute('id');

        // Get the section with all versions
        $response = $this->handleQuery(
            ItemDTO::create(['id' => $id])
        );

        if (!$response->isOk()) {
            $this->flashMessenger()->addErrorMessage('Unable to load version history');
            return $this->redirect()->toRoute('admin-dashboard/letter-management/letter-section');
        }

        $data = $response->getResult();

        $view = new ViewModel([
            'section' => $data,
            'versions' => $data['versions'] ?? [],
            'currentVersionId' => $data['currentVersion']['id'] ?? null,
        ]);

        $view->setTemplate('admin/letter-section/version-history');

        return $view;
    }

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
