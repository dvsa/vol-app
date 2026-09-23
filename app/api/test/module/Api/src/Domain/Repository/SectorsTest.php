<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\Sectors as Repo;
use Dvsa\Olcs\Api\Entity\Permits\Sectors as Entity;

final class SectorsTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * The QA options are a scalar projection, not entities: the form only needs the id and the two
     * translation keys.
     */
    public function testFetchQaOptions(): void
    {
        $options = [
            ['value' => '1', 'label' => 'sectors.chemicals.name', 'hint' => 'sectors.chemicals.description'],
            ['value' => '2', 'label' => 'sectors.food-products.name', 'hint' => 'sectors.food-products.description'],
        ];

        $qb = $this->newRealQb();
        $qb->stubbedQuery()->expects('getScalarResult')->withNoArgs()->andReturn($options);
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        $this->assertSame($options, $this->sut->fetchQaOptions());

        $this->assertSame(
            'SELECT s.id as value, s.nameKey as label, s.descriptionKey as hint'
            . ' FROM ' . Entity::class . ' s'
            . ' ORDER BY s.displayOrder ASC',
            $qb->getDQL(),
        );
    }
}
