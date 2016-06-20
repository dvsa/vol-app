<?php

namespace Admin\Controller;

use Admin\Form\Model\Form\PublicHoliday as AddEditForm;
use Dvsa\Olcs\Transfer\Command\System\PublicHoliday\Create as CreateCmd;
use Dvsa\Olcs\Transfer\Command\System\PublicHoliday\Delete as DeleteCmd;
use Dvsa\Olcs\Transfer\Command\System\PublicHoliday\Update as UpdateCmd;
use Dvsa\Olcs\Transfer\Query\System\PublicHoliday\Get as ItemQry;
use Dvsa\Olcs\Transfer\Query\System\PublicHoliday\GetList as ListQry;
use Olcs\Controller\AbstractInternalController;
use Olcs\Data\Mapper\PublicHoliday as AddEditFormMapper;

/**
 * Public Holiday Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PublicHolidayController extends AbstractInternalController
{
    protected $navigationId = 'admin-dashboard/admin-public-holiday';

    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    protected $routeIdentifier = 'holidayId';

    //  list
    protected $tableName = 'admin-public-holiday';
    protected $defaultTableSortField = 'publicHolidayDate';
    protected $defaultTableOrderField = 'DESC';
    protected $listDto = ListQry::class;

    //  add/edit
    protected $itemDto = ItemQry::class;
    protected $itemParams = ['id' => 'holidayId'];
    protected $formClass = AddEditForm::class;
    protected $mapperClass = AddEditFormMapper::class;
    protected $createCommand = CreateCmd::class;
    protected $updateCommand = UpdateCmd::class;

    protected $addContentTitle = 'Add holiday';
    protected $addSuccessMessage = 'New public holiday created';
    protected $editContentTitle = 'Edit holiday';
    protected $editSuccessMessage = 'Public holiday updated';

    //  delete
    protected $deleteParams = ['id' => 'holidayId'];
    protected $deleteCommand = DeleteCmd::class;
    protected $hasMultiDelete = false;
    protected $deleteModalTitle = 'Remove public holiday';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this public holiday?';
    protected $deleteSuccessMessage = 'The public holiday is removed';

    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Public holidays');

        return parent::indexAction();
    }
}
