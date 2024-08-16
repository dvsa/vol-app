<?php

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
class BusRegReadAuditTest extends AbstractReadAuditTest
{
    public function setUp(): void
    {
        $this->setUpSut(BusRegReadAudit::class, true);
    }

    public function testFetchOneOrMore()
    {
        parent::commonTestFetchOneOrMore('busReg');
    }

    public function testFetchList()
    {
        parent::commonTestFetchList(
            ReadBusReg::create(['id' => 111]),
            ' AND m.busReg = [[111]]'
        );
    }

    public function testDeleteOlderThan()
    {
        parent::commonTestDeleteOlderThan(BusRegReadAuditEntity::class);
    }
}
