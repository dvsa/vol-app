<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\EntityRepository;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\GracePeriod as Repo;
use Dvsa\Olcs\Api\Entity\Licence\GracePeriod as Entity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

final class GracePeriodTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class);
    }

    public function testFetchTypeReturnsAGracePeriodType(): void
    {
        $type = new RefData(Entity::TYPE_TM);

        $this->refDataLookup(Entity::TYPE_TM, $type);

        $this->assertSame($type, $this->sut->fetchType(Entity::TYPE_TM));
    }

    public function testFetchTypeRefusesAnythingThatIsNotAGracePeriodType(): void
    {
        $this->refDataLookup('lcat_gv', null);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchType('lcat_gv');
    }

    private function refDataLookup(string $id, ?RefData $found): void
    {
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('findOneBy')
            ->once()
            ->with(['id' => $id, 'refDataCategoryId' => Entity::TYPE_CATEGORY])
            ->andReturn($found);
        $this->em->shouldReceive('getRepository')->once()->with(RefData::class)->andReturn($repo);
    }
}
