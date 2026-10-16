<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\OrganisationPerson as OrganisationPersonRepo;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson as Entity;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\OrganisationPerson::class)]
final class OrganisationPersonTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    private const string PERSON_JOINS = ' LEFT JOIN m.person p LEFT JOIN p.title w0';

    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(OrganisationPersonRepo::class);
    }

    /**
     * Both ids are inlined rather than bound, because the repository passes the values straight
     * to expr()->eq().
     */
    public function testFetchByOrgAndPerson(): void
    {
        $organisation = m::mock(Organisation::class)->makePartial();
        $organisation->setId(123);

        $person = m::mock(Person::class)->makePartial();
        $person->setId(321);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs();
        $qb->stubbedQuery()->expects('getResult')->withNoArgs()->andReturn('Foo');

        $this->assertSame('Foo', $this->sut->fetchByOrgAndPerson($organisation, $person));

        $this->assertSame(
            'SELECT m' . self::FROM . ' WHERE m.organisation = 123 AND m.person = 321',
            $qb->getDQL(),
        );
    }

    public function testFetchListForOrganisation(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchListForOrganisation(34));

        // The person join here is an INNER JOIN written directly, not a with().
        $this->assertSame(
            'SELECT m' . self::FROM . ' INNER JOIN m.person p'
            . ' WHERE m.organisation = :organisationId',
            $qb->getDQL(),
        );
        $this->assertSame(34, $qb->getParameter('organisationId')->getValue());
    }

    public function testFetchCountForOrganisation(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchCountForOrganisation(34));

        $this->assertSame(
            'SELECT COUNT(m.person)' . self::FROM . ' INNER JOIN m.person p'
            . ' WHERE m.organisation = :organisationId',
            $qb->getDQL(),
        );
    }

    public function testFetchListForOrganisationAndPerson(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchListForOrganisationAndPerson(34, 98));

        $this->assertSame(
            'SELECT m, p, w0' . self::FROM . self::PERSON_JOINS
            . ' WHERE m.organisation = :organisationId AND m.person = :personId',
            $qb->getDQL(),
        );
        $this->assertSame(34, $qb->getParameter('organisationId')->getValue());
        $this->assertSame(98, $qb->getParameter('personId')->getValue());
    }

    public function testFetchListForPerson(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchListForPerson(354));

        $this->assertSame(
            'SELECT m, p, w0' . self::FROM . self::PERSON_JOINS . ' WHERE m.person = :personId',
            $qb->getDQL(),
        );
        $this->assertSame(354, $qb->getParameter('personId')->getValue());
    }
}
