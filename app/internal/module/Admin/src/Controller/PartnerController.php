<?php

/**
 * Partner Controller
 */
namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Command\User\CreatePartner as CreateDto;
use Dvsa\Olcs\Transfer\Command\User\UpdatePartner as UpdateDto;
use Dvsa\Olcs\Transfer\Command\User\DeletePartner as DeleteDto;
use Dvsa\Olcs\Transfer\Query\User\Partner as ItemDto;
use Dvsa\Olcs\Transfer\Query\User\PartnerList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\Data\Mapper\Partner as Mapper;
use Admin\Form\Model\Form\Partner as Form;

/**
 * Partner Controller
 */
class PartnerController extends AbstractInternalController implements
    PageLayoutProvider,
    PageInnerLayoutProvider
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

    public function getPageLayout()
    {
        return 'layout/admin-user-management-section';
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
        $this->placeholder()->setPlaceholder('pageTitle', 'Partners');
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
