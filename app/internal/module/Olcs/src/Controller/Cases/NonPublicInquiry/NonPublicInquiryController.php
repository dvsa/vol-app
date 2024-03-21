<?php

namespace Olcs\Controller\Cases\NonPublicInquiry;

use Dvsa\Olcs\Transfer\Command\Cases\NonPi\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\Cases\NonPi\Delete as DeleteDto;
use Dvsa\Olcs\Transfer\Command\Cases\NonPi\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Cases\NonPi\Listing as ListDto;
use Dvsa\Olcs\Transfer\Query\Cases\NonPi\Single as ItemDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\NonPi as MapperClass;
use Olcs\Form\Model\Form\NonPi as FormClass;

class NonPublicInquiryController extends AbstractInternalController implements CaseControllerInterface, LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_hearings_appeals_non_public_inquiry';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $defaultTableSortField = 'id';
    protected $tableName = 'NonPi';
    protected $listDto = ListDto::class;
    protected $listVars = ['case'];

    /**
     * get method Left View
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/cases/partials/left');

        return $view;
    }

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $detailsViewTemplate = 'sections/cases/pages/non-public-inquiry';
    protected $detailsViewPlaceholderName = 'details';
    protected $itemDto = ItemDto::class;
    // 'id' => 'conviction', to => from
    protected $itemParams = ['case'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = FormClass::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = MapperClass::class;
    protected $addContentTitle = 'Add non-public inquiry';
    protected $editContentTitle = 'Edit non-public inquiry';

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $createCommand = CreateDto::class;

    /**
     * Form data for the add form.
     *
     * Format is name => value
     * name => "route" means get value from route,
     * see conviction controller
     *
     * @var array
     */
    protected $defaultData = [
        'case' => 'route'
    ];

    protected $inlineScripts = ['forms/non-pi', 'shared/definition'];

    /**
     * Variables for controlling the delete action.
     * Command is required, as are itemParams from above
     */
    protected $deleteCommand = DeleteDto::class;

    protected $redirectConfig = [
        'add' => [
            'action' => 'details'
        ],
        'edit' => [
            'action' => 'details'
        ]
    ];

    /**
     * Action called if matched action does not exist
     *
     * @return array
     */
    public function notFoundAction()
    {
        return $this->viewBuilder()->buildViewFromTemplate($this->detailsViewTemplate);
    }
}
