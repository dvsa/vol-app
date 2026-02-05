<?php

namespace Admin\Controller;

use Admin\Form\Model\Form\Recipient as Form;
use Dvsa\Olcs\Transfer\Command\Publication\CreateRecipient as CreateDto;
use Dvsa\Olcs\Transfer\Command\Publication\DeleteRecipient as DeleteDto;
use Dvsa\Olcs\Transfer\Command\Publication\UpdateRecipient as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Publication\Recipient as ItemDto;
use Dvsa\Olcs\Transfer\Query\Publication\RecipientList as ListDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\Recipient as Mapper;

class RecipientController extends AbstractInternalController implements LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-publication/recipient';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $defaultTableSortField = 'contactName';
    protected $tableName = 'recipient';
    protected $listDto = ListDto::class;

    protected $addContentTitle = 'Add recipient';
    protected $editContentTitle = 'Edit recipient';

    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-publication',
                'navigationTitle' => 'Publications'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $itemDto = ItemDto::class;

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Form::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $createCommand = CreateDto::class;

    /**
     * Variables for controlling the delete action.
     * Command is required, as are itemParams from above
     */
    protected $deleteCommand = DeleteDto::class;

    private function setPageTitle()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Recipients');
    }

    #[\Override]
    public function indexAction()
    {
        $this->setPageTitle();

        return parent::indexAction();
    }

    #[\Override]
    public function detailsAction()
    {
        return $this->notFoundAction();
    }
}
