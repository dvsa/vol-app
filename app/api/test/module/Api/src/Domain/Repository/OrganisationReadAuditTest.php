<?php

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
class OrganisationReadAuditTest extends AbstractReadAuditTest
{
    public function setUp(): void
    {
        $this->setUpSut(OrganisationReadAudit::class, true);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testFetchOneOrMore()
    {
        parent::commonTestFetchOneOrMore('organisation');
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testFetchList()
    {
        parent::commonTestFetchList(
            ReadOrganisation::create(['id' => 111]),
            ' AND m.organisation = [[111]]'
        );
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testDeleteOlderThan()
    {
        parent::commonTestDeleteOlderThan(OrganisationReadAuditEntity::class);
    }
}
