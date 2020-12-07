<?php

namespace Admin\Controller;

use Admin\Form\Model\Form\Replacement;
use Admin\Data\Mapper\Replacement as ReplacementMapper;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Dvsa\Olcs\Transfer\Query\Replacement\GetList as ListDTO;
use Dvsa\Olcs\Transfer\Query\Replacement\ById as ItemDTO;
use Dvsa\Olcs\Transfer\Command\Replacement\Create as CreateDTO;
use Dvsa\Olcs\Transfer\Command\Replacement\Update as UpdateDTO;
use Laminas\View\Model\ViewModel;

/**
 * Editable Translations Controller
 */
class ReplacementsController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/content-management/replacements';
    protected $tableName = 'admin-replacements';
    protected $formClass = Replacement::class;

    protected $mapperClass = ReplacementMapper::class;

    protected $listDto = ListDto::class;
    protected $itemDto = ItemDto::class;
    protected $createCommand = CreateDTO::class;
    protected $updateCommand = UpdateDTO::class;

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    /**
     * Left View setting
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/content-management',
                'navigationTitle' => 'Replacements'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');
        return $view;
    }
}
