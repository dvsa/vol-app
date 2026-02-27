<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\FeeType;

use Dvsa\Olcs\Api\Domain\QueryHandler\FeeType\GetList as FeeTypeListHandler;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepo;
use Dvsa\Olcs\Transfer\Query\FeeType\GetList as FeeTypeListQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractListQueryHandlerTestCase;

/**
 * GetList Test
 */
class GetListTest extends AbstractListQueryHandlerTestCase
{
    protected $sutClass = FeeTypeListHandler::class;
    protected $sutRepo = 'FeeType';
    protected $qryClass = FeeTypeListQry::class;
    protected $repoClass = FeeTypeRepo::class;
}
