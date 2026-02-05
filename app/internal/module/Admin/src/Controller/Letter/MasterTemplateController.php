<?php

declare(strict_types=1);

namespace Admin\Controller\Letter;

use Admin\Data\Mapper\Letter\MasterTemplate as MasterTemplateMapper;
use Admin\Form\Model\Form\Letter\MasterTemplate as MasterTemplateForm;
use Dvsa\Olcs\Transfer\Command\Letter\MasterTemplate\Create as CreateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\MasterTemplate\Update as UpdateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\MasterTemplate\Delete as DeleteDTO;
use Dvsa\Olcs\Transfer\Query\Letter\MasterTemplate\Get as ItemDTO;
use Dvsa\Olcs\Transfer\Query\Letter\MasterTemplate\GetList as ListDTO;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class MasterTemplateController extends AbstractInternalController implements LeftViewProvider
{
    protected $tableName = 'admin-master-template';
    protected $defaultTableSortField = 'name';
    protected $defaultTableOrderField = 'ASC';

    protected $listDto = ListDTO::class;
    protected $itemDto = ItemDTO::class;
    protected $itemParams = ['id'];

    protected $formClass = MasterTemplateForm::class;
    protected $addFormClass = MasterTemplateForm::class;
    protected $mapperClass = MasterTemplateMapper::class;

    protected $createCommand = CreateDTO::class;
    protected $updateCommand = UpdateDTO::class;
    protected $deleteCommand = DeleteDTO::class;

    protected $addContentTitle = 'Add Master Template';
    protected $editContentTitle = 'Edit Master Template';

    protected $deleteModalTitle = 'Remove Master Template';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this master template?';
    protected $deleteSuccessMessage = 'The master template has been removed';

    protected $addSuccessMessage = 'Master template created successfully';
    protected $editSuccessMessage = 'Master template updated successfully';

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
