<?php

namespace Admin\Controller;

use Admin\Data\Mapper\FeatureToggle as FeatureToggleMapper;
use Admin\Form\Model\Form\FeatureToggle as FeatureToggleForm;
use Dvsa\Olcs\Transfer\Command\FeatureToggle\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\FeatureToggle\Delete as DeleteDto;
use Dvsa\Olcs\Transfer\Command\FeatureToggle\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\FeatureToggle\ById as ItemDto;
use Dvsa\Olcs\Transfer\Query\FeatureToggle\GetList as ListDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class FeatureToggleController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/admin-feature-toggle';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    // list
    protected $tableName = 'admin-feature-toggles';
    protected $defaultTableSortField = 'id';
    protected $defaultTableOrderField = 'ASC';
    protected $listDto = ListDto::class;

    // add/edit
    protected $itemDto = ItemDto::class;
    protected $formClass = FeatureToggleForm::class;
    protected $addFormClass = FeatureToggleForm::class;
    protected $mapperClass = FeatureToggleMapper::class;
    protected $createCommand = CreateDto::class;
    protected $updateCommand = UpdateDto::class;

    // delete
    protected $deleteCommand = DeleteDto::class;
    protected $hasMultiDelete = false;
    protected $deleteModalTitle = 'Remove feature toggle';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this feature toggle?';
    protected $deleteSuccessMessage = 'The feature toggle has been removed';

    protected $addContentTitle = 'Add feature toggle';
    protected $editContentTitle = 'Edit feature toggle';

    protected $tableViewTemplate = 'pages/feature-toggle/index';

    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-feature-toggle',
                'navigationTitle' => 'Feature toggles'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    #[\Override]
    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Feature toggles');

        return parent::indexAction();
    }
}
