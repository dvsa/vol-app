<?php

namespace Admin\Controller;

use Admin\Data\Mapper\PresidingTc as PresidingTcMapper;
use Admin\Form\Model\Form\PresidingTc;
use Dvsa\Olcs\Transfer\Command\Cases\PresidingTc\Create as CreateCmd;
use Dvsa\Olcs\Transfer\Command\Cases\PresidingTc\Delete as DeleteCmd;
use Dvsa\Olcs\Transfer\Command\Cases\PresidingTc\Update as UpdateCmd;
use Dvsa\Olcs\Transfer\Query\Cases\PresidingTc\ById as ItemDto;
use Dvsa\Olcs\Transfer\Query\Cases\PresidingTc\GetList as ListDto;
use Olcs\Controller\AbstractInternalController;

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
