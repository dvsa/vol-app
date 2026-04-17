<?php

declare(strict_types=1);

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
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ApplicationReadAuditTest extends AbstractReadAuditTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(ApplicationReadAudit::class, true);
    }

    public function testFetchOneOrMore(): void
    {
        parent::commonTestFetchOneOrMore('application');
    }

    public function testFetchList(): void
    {
        parent::commonTestFetchList(
            ReadApplication::create(['id' => 111]),
            ' AND m.application = [[111]]'
        );
    }

    public function testDeleteOlderThan(): void
    {
        parent::commonTestDeleteOlderThan(ApplicationReadAuditEntity::class);
    }
}
