<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\CasesReadAudit;
use Dvsa\Olcs\Api\Entity\Cases\CasesReadAudit as CasesReadAuditEntity;
use Dvsa\Olcs\Transfer\Query\Audit\ReadCase;
use Mockery as m;

/**
 * Cases Read Audit Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class CasesReadAuditTest extends AbstractReadAuditTestCase
{
    /** @var CasesReadAudit|m\MockInterface */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(CasesReadAudit::class, true);
    }

    public function testFetchOneOrMore(): void
    {
        parent::commonTestFetchOneOrMore('case');
    }

    public function testFetchList(): void
    {
        parent::commonTestFetchList(
            ReadCase::create(['id' => 111]),
            ' AND m.case = [[111]]'
        );
    }

    public function testDeleteOlderThan(): void
    {
        parent::commonTestDeleteOlderThan(CasesReadAuditEntity::class);
    }
}
