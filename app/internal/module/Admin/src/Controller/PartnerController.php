<?php

namespace Admin\Controller;

use Admin\Form\Model\Form\Partner as Form;
use Dvsa\Olcs\Transfer\Command\User\CreatePartner as CreateDto;
use Dvsa\Olcs\Transfer\Command\User\DeletePartner as DeleteDto;
use Dvsa\Olcs\Transfer\Command\User\UpdatePartner as UpdateDto;
use Dvsa\Olcs\Transfer\Query\User\Partner as ItemDto;
use Dvsa\Olcs\Transfer\Query\User\PartnerList as ListDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\Partner as Mapper;

class PartnerController extends AbstractInternalController implements LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-partner-management';

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
    protected $tableName = 'partner';
    protected $listDto = ListDto::class;

    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-user-management',
                'navigationTitle' => 'User management'
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
    protected $addContentTitle = 'Add partner';
    protected $editContentTitle = 'Edit partner';

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
        $this->placeholder()->setPlaceholder('pageTitle', 'User management');
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
