<?php

declare(strict_types=1);

/**
 * Bus Reg Read Audit Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\BusRegReadAudit;
use Dvsa\Olcs\Api\Entity\Bus\BusRegReadAudit as BusRegReadAuditEntity;
use Dvsa\Olcs\Transfer\Query\Audit\ReadBusReg;
use Mockery as m;

/**
 * Bus Reg Read Audit Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class BusRegReadAuditTest extends AbstractReadAuditTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(BusRegReadAudit::class, true);
    }

    public function testFetchOneOrMore(): void
    {
        parent::commonTestFetchOneOrMore('busReg');
    }

    public function testFetchList(): void
    {
        parent::commonTestFetchList(
            ReadBusReg::create(['id' => 111]),
            ' AND m.busReg = [[111]]'
        );
    }

    public function testDeleteOlderThan(): void
    {
        parent::commonTestDeleteOlderThan(BusRegReadAuditEntity::class);
    }
}
