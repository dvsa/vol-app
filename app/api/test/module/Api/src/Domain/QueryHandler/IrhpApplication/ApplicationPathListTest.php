<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication\ApplicationPathGroupList as ApplicationPathGroupListHandler;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationPathGroup as ApplicationPathRepo;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ApplicationPathGroupList as ApplicationPathGroupListQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractListQueryHandlerTestCase;

/**
 * ApplicationPathGroupList Test
 */
class ApplicationPathListTest extends AbstractListQueryHandlerTestCase
{
    protected $sutClass = ApplicationPathGroupListHandler::class;
    protected $sutRepo = 'ApplicationPathGroup';
    protected $qryClass = ApplicationPathGroupListQry::class;
    protected $repoClass = ApplicationPathRepo::class;
}
