<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\LicenceReadAudit;
use Dvsa\Olcs\Api\Entity\Licence\LicenceReadAudit as LicenceReadAuditEntity;
use Dvsa\Olcs\Transfer\Query\Audit\ReadLicence;
use Mockery as m;

/**
 * Licence Read Audit Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class LicenceReadAuditTest extends AbstractReadAuditTestCase
{
    /** @var LicenceReadAudit|m\MockInterface */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(LicenceReadAudit::class, true);
    }

    public function testFetchOneOrMore(): void
    {
        parent::commonTestFetchOneOrMore('licence');
    }

    public function testFetchList(): void
    {
        parent::commonTestFetchList(
            ReadLicence::create(['id' => 111]),
            ' AND m.licence = [[111]]'
        );
    }

    public function testDeleteOlderThan(): void
    {
        parent::commonTestDeleteOlderThan(LicenceReadAuditEntity::class);
    }
}
