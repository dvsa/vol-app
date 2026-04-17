<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\Repository\PhoneContact
 */
class PhoneContactTest extends RepositoryTestCase
{
    public const CONTACT_DETAILS_ID = 9999;

    /** @var  m\MockInterface */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(Repository\PhoneContact::class, true);
    }

    public function testBuildDefaultListQuery(): void
    {
        $mockQry = m::mock(\Dvsa\Olcs\Transfer\Query\QueryInterface::class);

        $mockQb = $this->createMockQb('{QUERY}');
        $mockQb
            ->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf()
            ->shouldReceive('withRefdata')->with()->once()->andReturnSelf()
            ->shouldReceive('with')->with('phoneContactType', 'pct')->once()->andReturnSelf();

        $this->sut->shouldReceive('getQueryBuilder')->with()->andReturn($mockQb);

        $this->sut->buildDefaultListQuery($mockQb, $mockQry, []);

        $expected = '{QUERY} SELECT pct.displayOrder as HIDDEN _type';

        $this->assertEquals($expected, $this->query);
    }

    public function testApplyListFilters(): void
    {
        /** @var QueryBuilder $mockQb */
        $mockQb = m::mock(QueryBuilder::class)
            ->shouldReceive('andWhere')->with('pc.contactDetails = :CONTACT_DETAILS_ID')->once()->andReturnSelf()
            ->shouldReceive('setParameter')->with('CONTACT_DETAILS_ID', self::CONTACT_DETAILS_ID)->once()->andReturn()
            ->getMock();

        /** @var QueryInterface | m\MockInterface $mockQuery */
        $mockQuery = m::mock(QueryInterface::class)
            ->shouldReceive('getContactDetailsId')->with()->andReturn(self::CONTACT_DETAILS_ID)
            ->getMock();

        $this->sut->applyListFilters($mockQb, $mockQuery);
    }
}
