<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\Sectors;
use Dvsa\Olcs\Api\Entity\Permits\Sectors as Entity;
use Mockery as m;

/**
 * Sectors test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class SectorsTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Sectors::class);
    }

    public function testFetchQaOptions(): void
    {
        $result = [
            [
                'value' => '1',
                'label' => 'sectors.chemicals.name',
                'hint' => 'sectors.chemicals.description'
            ],
            [
                'value' => '2',
                'label' => 'sectors.food-products.name',
                'hint' => 'sectors.food-products.description'
            ],
        ];

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('s.id as value, s.nameKey as label, s.descriptionKey as hint')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(Entity::class, 's')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('orderBy')
            ->with('s.displayOrder', 'ASC')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getScalarResult')
            ->once()
            ->andReturn($result);

        $this->assertEquals(
            $result,
            $this->sut->fetchQaOptions()
        );
    }
}
