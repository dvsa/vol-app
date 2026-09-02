<?php

declare(strict_types=1);

namespace Admin\Controller;

use Admin\Data\Mapper\LongText as LongTextMapper;
use Admin\Form\Model\Form\LongTextAdd;
use Admin\Form\Model\Form\LongTextEdit;
use Dvsa\Olcs\Transfer\Command\LongText\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\LongText\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\LongText\ById as ItemDto;
use Dvsa\Olcs\Transfer\Query\LongText\GetList as ListDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

final class LongTextController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/content-management/long-text';

    protected $tableName = 'admin-long-text';
    protected $listDto = ListDto::class;
    protected $itemDto = ItemDto::class;
    protected $createCommand = CreateDto::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = LongTextMapper::class;

    protected $formClass = LongTextEdit::class;
    protected $addFormClass = LongTextAdd::class;

    protected $defaultTableSortField = 'pageName';
    protected $defaultTableOrderField = 'ASC';

    protected $addContentTitle = 'Add Long Text';
    protected $editContentTitle = 'Edit Long Text';
    protected $addSuccessMessage = 'Long Text created';
    protected $editSuccessMessage = 'Long Text updated';

    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    #[\Override]
    public function getLeftView(): ViewModel
    {
        $view = new ViewModel([
            'navigationId' => 'admin-dashboard/content-management',
            'navigationTitle' => 'Long Text',
        ]);
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }
}
