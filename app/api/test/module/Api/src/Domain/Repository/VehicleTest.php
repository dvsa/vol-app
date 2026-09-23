<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\Vehicle as Repo;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle as Entity;

/**
 * VehicleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class VehicleTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class);
    }

    public function testFetchByVrm(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturnSelf();
        $qb->stubbedQuery()->expects('getResult')->withNoArgs()->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByVrm('ABC123'));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.vrm = :vrm',
            $qb->getDQL(),
        );
        $this->assertSame('ABC123', $qb->getParameter('vrm')->getValue());
    }
}
