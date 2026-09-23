<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use DateTime;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\PreviousPublicationByApplication;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\PreviousPublicationByLicence;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\PreviousPublicationByPi;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\UnpublishedApplication;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\UnpublishedBusReg;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\UnpublishedPi;
use Dvsa\Olcs\Api\Domain\Repository\PublicationLink as PublicationLinkRepo;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as Entity;
use Dvsa\Olcs\Transfer\Query\Publication\PublicationLinkList;
use Dvsa\Olcs\Transfer\Query\Publication\PublicationLinkTmList;
use Mockery as m;

final class PublicationLinkTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(PublicationLinkRepo::class, true);
    }

    public function testFetchByBusRegId(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULT']);

        $this->assertSame(['RESULT'], $this->sut->fetchByBusRegId(5));

        $this->assertSame(
            'SELECT m' . self::FROM . ' WHERE m.busReg = :busReg',
            $qb->getDQL(),
        );
        $this->assertSame(5, $qb->getParameter('busReg')->getValue());
    }

    /**
     * The optional filters are gated on method_exists(), so which ones apply is decided by the
     * bookmark query class rather than by the values it carries.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('unpublishedProvider')]
    public function testFetchSingleUnpublished(string $queryClass, array $data, string $expectedExtra): void
    {
        $result = m::mock(Entity::class);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn([$result]);

        $this->assertSame($result, $this->sut->fetchSingleUnpublished($queryClass::create($data)));

        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE m.publication = :byPublication AND m.publicationSection = :byPublicationSection'
            . $expectedExtra,
            $qb->getDQL(),
        );
        $this->assertSame(123, $qb->getParameter('byPublication')->getValue());
        $this->assertSame(456, $qb->getParameter('byPublicationSection')->getValue());
    }

    public static function unpublishedProvider(): \Iterator
    {
        $base = ['publication' => 123, 'publicationSection' => 456];

        yield 'application' => [
            UnpublishedApplication::class,
            $base + ['application' => 789],
            // The application bookmark declares no getLicence(), so that filter is skipped.
            ' AND m.application = :byApplication',
        ];
        yield 'pi' => [UnpublishedPi::class, $base + ['pi' => 789], ' AND m.pi = :byPi'];
        yield 'bus reg' => [UnpublishedBusReg::class, $base + ['busReg' => 789], ' AND m.busReg = :byBusReg'];
    }

    public function testFetchSingleUnpublishedReturnsNullWhenNothingMatches(): void
    {
        $this->createRealQb()->stubbedQuery()->expects('getResult')->andReturn([]);

        $this->assertNull($this->sut->fetchSingleUnpublished(
            UnpublishedPi::create(['publication' => 123, 'publicationSection' => 456, 'pi' => 789]),
        ));
    }

    /**
     * The previous publication is the highest publication number below the current one, in the
     * same traffic area and publication type.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('previousPublicationProvider')]
    public function testFetchPreviousPublicationNo(string $queryClass, array $data, string $expectedExtra): void
    {
        $result = m::mock(Entity::class);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn([$result]);

        $this->assertSame($result, $this->sut->fetchPreviousPublicationNo($queryClass::create($data)));

        $this->assertSame(
            'SELECT m, p' . self::FROM . ' LEFT JOIN m.publication p'
            . ' WHERE m.trafficArea = :byTrafficArea AND p.pubType = :byPubType'
            . ' AND p.publicationNo < :byPublicationNo'
            . $expectedExtra
            . ' ORDER BY p.publicationNo DESC',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getMaxResults());
    }

    public static function previousPublicationProvider(): \Iterator
    {
        $base = ['trafficArea' => 'M', 'pubType' => 'A&D', 'publicationNo' => 10];

        yield 'by pi' => [PreviousPublicationByPi::class, $base + ['pi' => 1], ' AND m.pi = :byPi'];
        yield 'by application' => [
            PreviousPublicationByApplication::class,
            $base + ['application' => 1],
            ' AND m.application = :byApplication',
        ];
        yield 'by licence' => [
            PreviousPublicationByLicence::class,
            $base + ['licence' => 1],
            ' AND m.licence = :byLicence',
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('listFilterProvider')]
    public function testApplyListFilters(string $queryClass, array $data, string $expectedWhere): void
    {
        $qb = $this->createRealQb();

        $this->sut->applyListFilters($qb, $queryClass::create($data));

        $this->assertSame('SELECT m' . self::FROM . ' WHERE ' . $expectedWhere, $qb->getDQL());
    }

    public static function listFilterProvider(): \Iterator
    {
        yield 'transport manager' => [
            PublicationLinkTmList::class,
            ['transportManager' => 11],
            'm.transportManager = :transportManager',
        ];
        yield 'licence' => [PublicationLinkList::class, ['licence' => 22], 'm.licence = :licence'];
        yield 'application' => [
            PublicationLinkList::class,
            ['application' => 610],
            'm.application = :application',
        ];
    }

    /**
     * Ineligible links are those held back by a publishAfterDate still in the future.
     */
    public function testFetchIneligiblePublicationLinks(): void
    {
        $publication = m::mock(PublicationEntity::class);
        $publication->shouldReceive('getId')->andReturn(1);

        $qb = $this->createRealQb()->willReturn(['RESULT']);

        $this->assertSame(['RESULT'], $this->sut->fetchIneligiblePublicationLinks($publication));

        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE m.publication = :publicationId AND m.publishAfterDate IS NOT NULL'
            . ' AND m.publishAfterDate > :today',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('publicationId')->getValue());
        $this->assertSame(new DateTime()->format('Y-m-d'), $qb->getParameter('today')->getValue());
    }
}
