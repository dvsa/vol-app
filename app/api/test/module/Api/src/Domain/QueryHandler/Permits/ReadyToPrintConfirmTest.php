<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\ReadyToPrintConfirm;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as Repo;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrintConfirm as ReadyToPrintConfirmQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractListQueryHandlerTest;

/**
 * ReadyToPrintConfirm Test
 */
class ReadyToPrintConfirmTest extends AbstractListQueryHandlerTest
{
    protected $sutClass = ReadyToPrintConfirm::class;
    protected $sutRepo = 'IrhpPermit';
    protected $qryClass = ReadyToPrintConfirmQry::class;
    protected $repoClass = Repo::class;
}
