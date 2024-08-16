<?php

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerReadAudit;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerReadAudit as TransportManagerReadAuditEntity;
use Dvsa\Olcs\Transfer\Query\Audit\ReadTransportManager;
use Mockery as m;

/**
 * Transport Manager Read Audit Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagerReadAuditTest extends AbstractReadAuditTest
{
    /** @var TransportManagerReadAudit|m\MockInterface */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(TransportManagerReadAudit::class, true);
    }

    public function testFetchOneOrMore()
    {
        parent::commonTestFetchOneOrMore('transportManager');
    }

    public function testFetchList()
    {
        parent::commonTestFetchList(
            ReadTransportManager::create(['id' => 111]),
            ' AND m.transportManager = [[111]]'
        );
    }

    public function testDeleteOlderThan()
    {
        parent::commonTestDeleteOlderThan(TransportManagerReadAuditEntity::class);
    }
}
