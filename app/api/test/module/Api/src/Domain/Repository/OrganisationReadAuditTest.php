<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\OrganisationReadAudit;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationReadAudit as OrganisationReadAuditEntity;
use Dvsa\Olcs\Transfer\Query\Audit\ReadOrganisation;
use Mockery as m;

/**
 * Organisation Read Audit Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class OrganisationReadAuditTest extends AbstractReadAuditTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(OrganisationReadAudit::class, true);
    }

    public function testFetchOneOrMore(): void
    {
        parent::commonTestFetchOneOrMore('organisation');
    }

    public function testFetchList(): void
    {
        parent::commonTestFetchList(
            ReadOrganisation::create(['id' => 111]),
            ' AND m.organisation = [[111]]'
        );
    }

    public function testDeleteOlderThan(): void
    {
        parent::commonTestDeleteOlderThan(OrganisationReadAuditEntity::class);
    }
}
