<?php

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationReadAudit;
use Dvsa\Olcs\Api\Entity\Application\ApplicationReadAudit as ApplicationReadAuditEntity;
use Dvsa\Olcs\Transfer\Query\Audit\ReadApplication;
use Mockery as m;

/**
 * Application Read Audit Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationReadAuditTest extends AbstractReadAuditTest
{
    public function setUp(): void
    {
        $this->setUpSut(ApplicationReadAudit::class, true);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testFetchOneOrMore()
    {
        parent::commonTestFetchOneOrMore('application');
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testFetchList()
    {
        parent::commonTestFetchList(
            ReadApplication::create(['id' => 111]),
            ' AND m.application = [[111]]'
        );
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testDeleteOlderThan()
    {
        parent::commonTestDeleteOlderThan(ApplicationReadAuditEntity::class);
    }
}
