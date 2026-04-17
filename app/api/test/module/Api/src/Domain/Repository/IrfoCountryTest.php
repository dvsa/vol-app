<?php

declare(strict_types=1);

/**
 * IrfoCountryTest
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\IrfoCountry as Repo;
use Mockery as m;

/**
 * IrfoCountry Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class IrfoCountryTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testApplyListFilters(): void
    {
        $this->setUpSut(Repo::class, true);

        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $mockQ = m::mock(\Dvsa\Olcs\Transfer\Query\QueryInterface::class);
        $mockQb->shouldReceive('orderBy')->with('m.description', 'ASC')->once()->andReturnSelf();

        $this->sut->applyListFilters($mockQb, $mockQ);
    }
}
