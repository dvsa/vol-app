<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\ReadyToPrint;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as Repo;
use Dvsa\Olcs\Transfer\Query\Permits\ReadyToPrint as ReadyToPrintQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractListQueryHandlerTest;

/**
 * ReadyToPrint Test
 */
class ReadyToPrintTest extends AbstractListQueryHandlerTest
{
    protected $sutClass = ReadyToPrint::class;
    protected $sutRepo = 'IrhpPermit';
    protected $qryClass = ReadyToPrintQry::class;
    protected $repoClass = Repo::class;
}
