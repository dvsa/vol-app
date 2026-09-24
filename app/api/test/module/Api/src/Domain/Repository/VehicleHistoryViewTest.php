<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\VehicleHistoryView as VehicleHistoryViewRepo;
use Dvsa\Olcs\Api\Entity\View\VehicleHistoryView as Entity;

final class VehicleHistoryViewTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(VehicleHistoryViewRepo::class);
    }

    public function testFetchByVrm(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturnSelf();
        $qb->stubbedQuery()->expects('getArrayResult')->withNoArgs()->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByVrm('ABC123'));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m'
            . ' WHERE m.vrm = :vrm AND m.id IS NOT NULL'
            . ' ORDER BY m.specifiedDate DESC',
            $qb->getDQL(),
        );
        $this->assertSame('ABC123', $qb->getParameter('vrm')->getValue());
    }
}
