<?php

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplicationReadAudit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplicationReadAudit as IrhpApplicationReadAuditEntity;
use Dvsa\Olcs\Transfer\Query\Audit\ReadIrhpApplication;
use Mockery as m;

/**
 * Irhp Application Read Audit Test
 */
class IrhpApplicationReadAuditTest extends AbstractReadAuditTest
{
    public function setUp(): void
    {
        $this->setUpSut(IrhpApplicationReadAudit::class, true);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testFetchOneOrMore()
    {
        parent::commonTestFetchOneOrMore('irhpApplication');
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testFetchList()
    {
        parent::commonTestFetchList(
            ReadIrhpApplication::create(['id' => 111]),
            ' AND m.irhpApplication = [[111]]'
        );
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testDeleteOlderThan()
    {
        parent::commonTestDeleteOlderThan(IrhpApplicationReadAuditEntity::class);
    }
}
