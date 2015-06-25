<?php

/**
 * Recipient Controller
 */
namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Command\Publication\CreateRecipient as CreateDto;
use Dvsa\Olcs\Transfer\Command\Publication\UpdateRecipient as UpdateDto;
use Dvsa\Olcs\Transfer\Command\Publication\DeleteRecipient as DeleteDto;
use Dvsa\Olcs\Transfer\Query\Publication\Recipient as ItemDto;
use Dvsa\Olcs\Transfer\Query\Publication\RecipientList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\Data\Mapper\Recipient as Mapper;
use Admin\Form\Model\Form\Recipient as Form;

/**
 * Recipient Controller
 */
class RecipientController extends AbstractInternalController implements
    PageLayoutProvider,
    PageInnerLayoutProvider
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

    public function getPageLayout()
    {
        return 'layout/admin-publication-section';
    }

    public function getPageInnerLayout()
    {
        return 'layout/wide-layout';
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

    public function indexAction()
    {
        $this->setPageTitle();

        return parent::indexAction();
    }

    public function detailsAction()
    {
        return $this->notFoundAction();
    }
}
