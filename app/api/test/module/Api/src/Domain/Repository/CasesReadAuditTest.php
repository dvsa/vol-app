<?php

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
class CasesReadAuditTest extends AbstractReadAuditTest
{
    /** @var CasesReadAudit|m\MockInterface */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(CasesReadAudit::class, true);
    }

    public function testFetchOneOrMore()
    {
        parent::commonTestFetchOneOrMore('case');
    }

    public function testFetchList()
    {
        parent::commonTestFetchList(
            ReadCase::create(['id' => 111]),
            ' AND m.case = [[111]]'
        );
    }

    public function testDeleteOlderThan()
    {
        parent::commonTestDeleteOlderThan(CasesReadAuditEntity::class);
    }
}
