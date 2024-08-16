<?php

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
class LicenceReadAuditTest extends AbstractReadAuditTest
{
    /** @var LicenceReadAudit|m\MockInterface */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(LicenceReadAudit::class, true);
    }

    public function testFetchOneOrMore()
    {
        parent::commonTestFetchOneOrMore('licence');
    }

    public function testFetchList()
    {
        parent::commonTestFetchList(
            ReadLicence::create(['id' => 111]),
            ' AND m.licence = [[111]]'
        );
    }

    public function testDeleteOlderThan()
    {
        parent::commonTestDeleteOlderThan(LicenceReadAuditEntity::class);
    }
}
