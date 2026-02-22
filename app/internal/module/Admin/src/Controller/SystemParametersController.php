<?php

namespace Admin\Controller;

use Admin\Form\Model\Form\SystemParameter as SystemParameterForm;
use Dvsa\Olcs\Transfer\Command\SystemParameter\CreateSystemParameter as CreateDto;
use Dvsa\Olcs\Transfer\Command\SystemParameter\DeleteSystemParameter as DeleteDto;
use Dvsa\Olcs\Transfer\Command\SystemParameter\UpdateSystemParameter as UpdateDto;
use Dvsa\Olcs\Transfer\Query\SystemParameter\SystemParameter as ItemDto;
use Dvsa\Olcs\Transfer\Query\SystemParameter\SystemParameterList as ListDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\SystemParameter as SystemParameterMapper;

class SystemParametersController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/admin-manage-system-parameters';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    // list
    protected $tableName = 'admin-system-parameters';
    protected $defaultTableSortField = 'id';
    protected $defaultTableOrderField = 'ASC';
    protected $listDto = ListDto::class;

    // add/edit
    protected $itemDto = ItemDto::class;
    protected $itemParams = ['id' => 'sp'];
    protected $formClass = SystemParameterForm::class;
    protected $addFormClass = SystemParameterForm::class;
    protected $mapperClass = SystemParameterMapper::class;
    protected $createCommand = CreateDto::class;
    protected $updateCommand = UpdateDto::class;
    protected $routeIdentifier = 'sp';

    // delete
    protected $deleteParams = ['id' => 'sp'];
    protected $deleteCommand = DeleteDto::class;
    protected $hasMultiDelete = false;
    protected $deleteModalTitle = 'Remove system parameter';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this system parameter?';
    protected $deleteSuccessMessage = 'The system parameter is removed';

    protected $addContentTitle = 'Add system parameter';
    protected $editContentTitle = 'Edit system parameter';

    protected $tableViewTemplate = 'pages/system-parameters/system-parameters';

    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-manage-system-parameters',
                'navigationTitle' => 'System parameters'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    #[\Override]
    public function indexAction()
    {

        $this->placeholder()->setPlaceholder('pageTitle', 'System parameters');

        return parent::indexAction();
    }

    public function alterFormForEdit($form)
    {
        $formHelper = $this->formHelperService;

        // id is disabled in hidden mode so it could be empty after form validation failed
        $form->get('system-parameter-details')
            ->get('id')
            ->setValue(
                $form->get('system-parameter-details')->get('hiddenId')->getValue()
            );
        $formHelper->disableElement($form, 'system-parameter-details->id');
        $formHelper->remove($form, 'form-actions->addAnother');

        return $form;
    }
}
