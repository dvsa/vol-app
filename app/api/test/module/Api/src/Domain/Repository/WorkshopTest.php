<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\Workshop as WorkshopRepo;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Workshop as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class WorkshopTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(WorkshopRepo::class, true);
    }

    public function testFetchUsingIdThrowsWhenNothingFound(): void
    {
        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getId')->andReturn(111);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(null);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchUsingId($command, Query::HYDRATE_OBJECT, 1);
    }

    public function testFetchUsingIdWithResults(): void
    {
        $result = m::mock(Entity::class);

        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getId')->andReturn(111);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn([$result]);
        $this->em->expects('lock')->with($result, LockMode::OPTIMISTIC, 1);

        $this->assertSame($result, $this->sut->fetchUsingId($command, Query::HYDRATE_OBJECT, 1));

        // withContactDetails() expands to the whole contact-details tree.
        $this->assertSame(
            'SELECT m, m_cd, m_cd_a, m_cd_a_cc, m_cd_pc, w0, w1 FROM ' . Entity::class . ' m'
            . ' LEFT JOIN m.contactDetails m_cd LEFT JOIN m_cd.address m_cd_a'
            . ' LEFT JOIN m_cd_a.countryCode m_cd_a_cc LEFT JOIN m_cd.phoneContacts m_cd_pc'
            . ' LEFT JOIN m_cd.contactType w0 LEFT JOIN m_cd_pc.phoneContactType w1'
            . ' WHERE m.id = :byId',
            $qb->getDQL(),
        );
    }

    public function testFetchForLicence(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchForLicence(2017));

        $this->assertSame(
            'SELECT m, cd, w0 FROM ' . Entity::class . ' m'
            . ' LEFT JOIN m.contactDetails cd LEFT JOIN cd.address w0'
            . ' WHERE m.licence = :licenceId',
            $qb->getDQL(),
        );
        $this->assertSame(2017, $qb->getParameter('licenceId')->getValue());
    }

    public function testApplyListFiltersForALicenceQuery(): void
    {
        $qb = $this->createRealQb();

        $query = m::mock(\Dvsa\Olcs\Transfer\Query\Licence\Safety::class);
        $query->expects('getId')->withNoArgs()->andReturn(34);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.licence = :byLicence',
            $qb->getDQL(),
        );
        $this->assertSame(34, $qb->getParameter('byLicence')->getValue());
    }

    /**
     * An application query filters on the application's licence, not the application itself.
     */
    public function testApplyListFiltersForAnApplicationQuery(): void
    {
        $qb = $this->createRealQb();

        $query = m::mock(\Dvsa\Olcs\Transfer\Query\Application\Safety::class);
        $query->expects('getId')->withNoArgs()->andReturn(134);

        $application = m::mock();
        $application->expects('getLicence->getId')->withNoArgs()->andReturn(24);
        $this->em->expects('getReference')->with(Application::class, 134)->andReturn($application);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.licence = :byLicence',
            $qb->getDQL(),
        );
        $this->assertSame(24, $qb->getParameter('byLicence')->getValue());
    }
}
