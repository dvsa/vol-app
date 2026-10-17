<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\PhoneContact::class)]
final class PhoneContactTest extends RepositoryTestCase
{
    public const int CONTACT_DETAILS_ID = 9999;

    /** @var  m\MockInterface */
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repository\PhoneContact::class, true);
    }

    public function testBuildDefaultListQuery(): void
    {
        $qb = $this->createRealQb();

        $this->sut->buildDefaultListQuery($qb, m::mock(QueryInterface::class), []);

        // The explicit pct alias replaces the automatically generated refdata alias,
        // so phoneContactType is joined only once.
        $this->assertSame(
            'SELECT pc, pct, pct.displayOrder as HIDDEN _type FROM ' . Entity::class . ' pc'
            . ' LEFT JOIN pc.phoneContactType pct',
            $qb->getDQL(),
        );
    }

    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $mockQuery = m::mock(QueryInterface::class)
            ->shouldReceive('getContactDetailsId')->with()->andReturn(self::CONTACT_DETAILS_ID)
            ->getMock();

        $this->sut->applyListFilters($qb, $mockQuery);

        $this->assertSame(
            'SELECT pc FROM ' . Entity::class . ' pc WHERE pc.contactDetails = :CONTACT_DETAILS_ID',
            $qb->getDQL(),
        );
        $this->assertSame(self::CONTACT_DETAILS_ID, $qb->getParameter('CONTACT_DETAILS_ID')->getValue());
    }
}
