<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as Repo;
use Dvsa\Olcs\Api\Domain\Repository\Query\Organisation\FixIsIrfo;
use Dvsa\Olcs\Api\Domain\Repository\Query\Organisation\FixIsUnlicenced;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as Entity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\Organisation\CpidOrganisation;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\Organisation::class)]
final class OrganisationTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' o';

    /** withRefdata() on Organisation joins type and cpid. */
    private const string REFDATA = ' LEFT JOIN o.type w0 LEFT JOIN o.cpid w1';

    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchBusinessDetailsById(): void
    {
        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getId')->andReturn(111);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn([['foo' => 'bar']]);

        $this->assertSame(['foo' => 'bar'], $this->sut->fetchBusinessDetailsUsingId($command));

        $this->assertSame(
            'SELECT o, w0, w1, o_cd, o_cd_a, o_cd_a_cc, o_cd_pc, w2, w3' . self::FROM . self::REFDATA
            . ' LEFT JOIN o.contactDetails o_cd LEFT JOIN o_cd.address o_cd_a'
            . ' LEFT JOIN o_cd_a.countryCode o_cd_a_cc LEFT JOIN o_cd.phoneContacts o_cd_pc'
            . ' LEFT JOIN o_cd.contactType w2 LEFT JOIN o_cd_pc.phoneContactType w3'
            . ' WHERE o.id = :byId',
            $qb->getDQL(),
        );
    }

    public function testFetchBusinessDetailsByIdNotFound(): void
    {
        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getId')->andReturn(111);

        $this->createRealQb()->stubbedQuery()->expects('getResult')->andReturn(null);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchBusinessDetailsUsingId($command);
    }

    /**
     * The IRFO view hangs its contact details off irfoContactDetails rather than the default
     * contactDetails association.
     */
    public function testFetchIrfoDetailsById(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['result']);

        $this->assertSame('result', $this->sut->fetchIrfoDetailsById(111));

        $this->assertSame(
            'SELECT o, w0, w1, w2, w3, tn, o_cd, o_cd_a, o_cd_a_cc, o_cd_pc, w4, w5'
            . self::FROM . self::REFDATA
            . ' LEFT JOIN o.irfoNationality w2 LEFT JOIN o.irfoPartners w3'
            . ' LEFT JOIN o.tradingNames tn LEFT JOIN o.irfoContactDetails o_cd'
            . ' LEFT JOIN o_cd.address o_cd_a LEFT JOIN o_cd_a.countryCode o_cd_a_cc'
            . ' LEFT JOIN o_cd.phoneContacts o_cd_pc LEFT JOIN o_cd.contactType w4'
            . ' LEFT JOIN o_cd_pc.phoneContactType w5'
            . ' WHERE o.id = :byId',
            $qb->getDQL(),
        );
    }

    /**
     * The status filter is applied in PHP after the query, not in DQL — the query fetches every
     * organisation with the company number and the licences are sifted in a loop.
     */
    public function testGetByCompanyOrLlpNo(): void
    {
        $licence = m::mock();
        $licence->shouldReceive('getStatus->getId')->andReturn(LicenceEntity::LICENCE_STATUS_VALID);

        $organisation = m::mock(Entity::class);
        $organisation->shouldReceive('getLicences')->andReturn([$licence]);

        $qb = $this->createRealQb()->willReturn([$organisation]);

        $this->assertSame([$organisation], $this->sut->getByCompanyOrLlpNo('01234567'));

        $this->assertSame(
            'SELECT o, w0, w1, w2' . self::FROM . self::REFDATA . ' LEFT JOIN o.licences w2'
            . ' WHERE o.companyOrLlpNo = :companyNumber',
            $qb->getDQL(),
        );
        $this->assertSame('01234567', $qb->getParameter('companyNumber')->getValue());
    }

    public function testGetByCompanyOrLlpNoNotFoundWhenNoLicenceHasAnActiveStatus(): void
    {
        $licence = m::mock();
        $licence->shouldReceive('getStatus->getId')->andReturn(LicenceEntity::LICENCE_STATUS_REVOKED);

        $organisation = m::mock(Entity::class);
        $organisation->shouldReceive('getLicences')->andReturn([$licence]);

        $this->createRealQb()->willReturn([$organisation]);

        $this->expectException(NotFoundException::class);

        $this->sut->getByCompanyOrLlpNo('01234567');
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('cpidProvider')]
    public function testFetchByStatusPaginated(?string $cpid, string $expectedWhere): void
    {
        $qb = $this->createRealQb();

        // A non-null cpid is resolved to a RefData reference before being bound.
        $this->em->shouldReceive('getReference')->andReturn(m::mock(RefData::class));

        $query = m::mock(CpidOrganisation::class)->makePartial();
        $query->shouldReceive('getCpid')->andReturn($cpid);
        $query->shouldReceive('getPage')->andReturn(1);
        $query->shouldReceive('getLimit')->andReturn(10);

        $this->sut->expects('fetchPaginatedList')->with($qb, Query::HYDRATE_OBJECT)->andReturn(['foo']);
        $this->sut->expects('fetchPaginatedCount')->with($qb)->andReturn(1);

        $this->assertSame(['result' => ['foo'], 'count' => 1], $this->sut->fetchByStatusPaginated($query));

        $this->assertSame(
            'SELECT o, w0, w1' . self::FROM . self::REFDATA . $expectedWhere . ' ORDER BY o.name ASC',
            $qb->getDQL(),
        );
    }

    public static function cpidProvider(): \Iterator
    {
        yield 'no cpid means unassigned only' => [null, ' WHERE o.cpid IS NULL'];
        yield 'a cpid filters on it' => ['cpid_1', ' WHERE o.cpid = :cpid'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('cpidProvider')]
    public function testFetchAllByStatusForCpidExport(?string $status, string $expectedWhere): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('toIterable')->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchAllByStatusForCpidExport($status));

        // The explicit select() runs last and replaces the joined entity select list.
        $this->assertSame(
            'SELECT o.id, o.name, r.id AS cpid' . self::FROM . ' LEFT JOIN o.cpid r' . $expectedWhere,
            $qb->getDQL(),
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('fixProvider')]
    public function testFixQueries(string $method, string $queryClass, int $rowCount): void
    {
        $statement = m::mock();
        $statement->expects('rowCount')->withNoArgs()->andReturn($rowCount);

        $query = m::mock();
        $query->expects('execute')->withNoArgs()->andReturn($statement);

        $this->dbQueryService->expects('get')->with($queryClass)->andReturn($query);

        $this->assertSame($rowCount, $this->sut->{$method}());
    }

    public static function fixProvider(): \Iterator
    {
        yield 'isIrfo' => ['fixIsIrfo', FixIsIrfo::class, 52];
        yield 'isUnlicenced' => ['fixIsUnlicenced', FixIsUnlicenced::class, 12];
    }

    public function testGetAllOrganisationsForCompaniesHouse(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->getAllOrganisationsForCompaniesHouse());

        $this->assertSame(
            'SELECT DISTINCT o.companyOrLlpNo' . self::FROM
            . ' INNER JOIN ' . LicenceEntity::class . ' l WITH l.organisation = o.id'
            . ' WHERE l.status IN(:licenceStatuses) AND o.companyOrLlpNo IS NOT NULL'
            . ' AND o.type IN(:orgTypes)',
            $qb->getDQL(),
        );
        $this->assertSame(
            [
                LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION,
                LicenceEntity::LICENCE_STATUS_SUSPENDED,
                LicenceEntity::LICENCE_STATUS_VALID,
                LicenceEntity::LICENCE_STATUS_CURTAILED,
                LicenceEntity::LICENCE_STATUS_GRANTED,
            ],
            $qb->getParameter('licenceStatuses')->getValue(),
        );
        $this->assertSame(
            [Entity::ORG_TYPE_REGISTERED_COMPANY, Entity::ORG_TYPE_LLP],
            $qb->getParameter('orgTypes')->getValue(),
        );
    }
}
