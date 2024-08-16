<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases\Report;

use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractListQueryHandlerTest;

/**
 * @covers \Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Report\OpenList
 */
class OpenListTest extends AbstractListQueryHandlerTest
{
    protected $sutClass = \Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Report\OpenList::class;
    protected $repoClass = \Dvsa\Olcs\Api\Domain\Repository\Cases::class;
    protected $sutRepo = 'Cases';
    protected $qryClass = \Dvsa\Olcs\Transfer\Query\Cases\Report\OpenList::class;
    protected bool $modifiesTrafficAreasForRbac = true;
}
