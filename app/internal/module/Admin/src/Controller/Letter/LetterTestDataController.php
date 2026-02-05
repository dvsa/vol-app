<?php

declare(strict_types=1);

namespace Admin\Controller\Letter;

use Admin\Data\Mapper\Letter\LetterTestData as LetterTestDataMapper;
use Admin\Form\Model\Form\Letter\LetterTestData as LetterTestDataForm;
use Dvsa\Olcs\Transfer\Command\Letter\LetterTestData\Create as CreateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterTestData\Update as UpdateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterTestData\Delete as DeleteDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterTestData\Get as ItemDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterTestData\GetList as ListDTO;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class LetterTestDataController extends AbstractInternalController implements LeftViewProvider
{
    protected $tableName = 'admin-letter-test-data';
    protected $defaultTableSortField = 'name';
    protected $defaultTableOrderField = 'ASC';

    protected $listDto = ListDTO::class;
    protected $itemDto = ItemDTO::class;
    protected $itemParams = ['id'];

    protected $formClass = LetterTestDataForm::class;
    protected $addFormClass = LetterTestDataForm::class;
    protected $mapperClass = LetterTestDataMapper::class;

    protected $createCommand = CreateDTO::class;
    protected $updateCommand = UpdateDTO::class;
    protected $deleteCommand = DeleteDTO::class;

    protected $addContentTitle = 'Add Test Data';
    protected $editContentTitle = 'Edit Test Data';

    protected $deleteModalTitle = 'Remove Test Data';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this test data?';
    protected $deleteSuccessMessage = 'The test data has been removed';

    protected $addSuccessMessage = 'Test data created successfully';
    protected $editSuccessMessage = 'Test data updated successfully';

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
