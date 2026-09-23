<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationOrganisationPerson as Repo;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson as Entity;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Mockery as m;

final class ApplicationOrganisationPersonTest extends RepositoryTestCase
{
    private const string SELECT = 'SELECT m, p, w0 FROM ' . Entity::class . ' m'
        . ' LEFT JOIN m.person p LEFT JOIN p.title w0';

    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class);
    }

    public function testFetchListForApplication(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchListForApplication(34));

        $this->assertSame(self::SELECT . ' WHERE m.application = :applicationId', $qb->getDQL());
        $this->assertSame(34, $qb->getParameter('applicationId')->getValue());
    }

    /**
     * The two lookups differ only in which person column they match, so a replaced person can
     * still be found by the person they replaced.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('personLookupProvider')]
    public function testFetchForApplicationAndPerson(string $method, string $column): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULT']);

        $this->assertSame('RESULT', $this->sut->{$method}(34, 76));

        $this->assertSame(
            self::SELECT . ' WHERE m.application = :applicationId AND m.' . $column . ' = :personId',
            $qb->getDQL(),
        );
        $this->assertSame(34, $qb->getParameter('applicationId')->getValue());
        $this->assertSame(76, $qb->getParameter('personId')->getValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('personLookupProvider')]
    public function testFetchForApplicationAndPersonNotFound(string $method, string $column): void
    {
        $this->createRealQb()->willReturn([]);

        // $column is unused here; the provider is shared with the success case above.
        unset($column);

        $this->expectException(NotFoundException::class);

        $this->sut->{$method}(34, 76);
    }

    public static function personLookupProvider(): \Iterator
    {
        yield 'current person' => ['fetchForApplicationAndPerson', 'person'];
        yield 'original person' => ['fetchForApplicationAndOriginalPerson', 'originalPerson'];
    }

    public function testDeleteForPerson(): void
    {
        $person = m::mock(Person::class);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs();

        $this->sut->deleteForPerson($person);

        $this->assertSame(
            'DELETE ' . Entity::class . ' m'
            . ' WHERE m.person = :person OR m.originalPerson = :person',
            $qb->getDQL(),
        );
        $this->assertSame($person, $qb->getParameter('person')->getValue());
    }
}
