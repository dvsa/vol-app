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

    public function testFetchOneOrMore()
    {
        parent::commonTestFetchOneOrMore('application');
    }

    public function testFetchList()
    {
        parent::commonTestFetchList(
            ReadApplication::create(['id' => 111]),
            ' AND m.application = [[111]]'
        );
    }

    public function testDeleteOlderThan()
    {
        parent::commonTestDeleteOlderThan(ApplicationReadAuditEntity::class);
    }
}
