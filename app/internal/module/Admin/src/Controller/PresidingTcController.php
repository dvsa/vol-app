<?php

namespace Admin\Controller;

use Admin\Form\Model\Form\PresidingTc;
use Olcs\Controller\AbstractInternalController;
use Dvsa\Olcs\Transfer\Query\Cases\PresidingTc\ById as ItemDto;
use Dvsa\Olcs\Transfer\Query\Cases\PresidingTc\GetList as ListDto;
use Dvsa\Olcs\Transfer\Command\Cases\PresidingTc\Update as UpdateCmd;
use Dvsa\Olcs\Transfer\Command\Cases\PresidingTc\Create as CreateCmd;
use Dvsa\Olcs\Transfer\Command\Cases\PresidingTc\Delete as DeleteCmd;
use Admin\Data\Mapper\PresidingTc as PresidingTcMapper;

/**
 * Presiding TC admin controller
 */
class PresidingTcController extends AbstractInternalController
{
    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    protected $tableName = 'admin-presiding-tcs';
    protected $defaultTableSortField = 'name';
    protected $defaultTableOrderField = 'ASC';

    protected $deleteCommand = DeleteCmd::class;
    protected $createCommand = CreateCmd::class;
    protected $listDto = ListDto::class;
    protected $itemDto = ItemDto::class;
    protected $updateCommand = UpdateCmd::class;

    protected $navigationId = 'admin-dashboard/presiding-tcs';

    protected $formClass = PresidingTc::class;

    protected $mapperClass = PresidingTcMapper::class;
}
