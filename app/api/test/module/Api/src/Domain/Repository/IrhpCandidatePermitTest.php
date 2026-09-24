<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit as Repo;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit as Entity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetListByIrhpApplication;
use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetListByIrhpApplicationUnpaged;

final class IrhpCandidatePermitTest extends RepositoryTestCase
{
    public const int IRHP_APPLICATION_ID = 10;

    private const string LIST_FROM = 'SELECT m, w0, w1, ipa, ia FROM ' . Entity::class . ' m'
        . ' LEFT JOIN m.requestedEmissionsCategory w0 LEFT JOIN m.assignedEmissionsCategory w1'
        . ' LEFT JOIN m.irhpPermitApplication ipa LEFT JOIN ipa.irhpApplication ia';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * Both list query classes take the same filter path; only paging differs, which does not
     * reach the DQL.
     *
     * The old fixture passed 'order' => 'id', 'sort' => 'ASC' — transposed. The double
     * concatenated whatever it was given, so nothing noticed; a real QueryBuilder rejects
     * 'id' as a sort direction outright.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('listQueryProvider')]
    public function testFetchList(string $queryClass, array $extraParams, string $expectedWhere): void
    {
        $qb = $this->createRealQb();
        $this->sut->expects('fetchPaginatedList')->andReturn(['RESULTS']);

        $query = $queryClass::create([
            'irhpApplication' => self::IRHP_APPLICATION_ID,
            'page' => 1,
            'limit' => 25,
            'sort' => 'id',
            'order' => 'ASC',
            ...$extraParams,
        ]);

        $this->assertSame(['RESULTS'], $this->sut->fetchList($query));

        $this->assertSame(self::LIST_FROM . $expectedWhere . ' ORDER BY m.id ASC', $qb->getDQL());
        $this->assertSame(self::IRHP_APPLICATION_ID, $qb->getParameter('irhpApplicationId')->getValue());
    }

    public static function listQueryProvider(): \Iterator
    {
        $awaitingFee = ' WHERE m.successful = :successful AND ia.status = :status'
            . ' AND ipa.irhpApplication = :irhpApplicationId';
        $preGrant = ' WHERE ia.status IN(:status) AND ipa.irhpApplication = :irhpApplicationId';

        foreach ([GetListByIrhpApplication::class, GetListByIrhpApplicationUnpaged::class] as $class) {
            $short = substr((string) strrchr($class, '\\'), 1);
            yield "{$short}: awaiting fee" => [$class, [], $awaitingFee];
            yield "{$short}: wanted only" => [
                $class,
                ['wantedOnly' => true],
                $awaitingFee . ' AND m.wanted = :wanted',
            ];
            yield "{$short}: pre grant" => [$class, ['isPreGrant' => true], $preGrant];
        }
    }

    public function testFetchListBindsTheAwaitingFeeStatus(): void
    {
        $qb = $this->createRealQb();
        $this->sut->expects('fetchPaginatedList')->andReturn(['RESULTS']);

        $this->sut->fetchList(GetListByIrhpApplication::create([
            'irhpApplication' => self::IRHP_APPLICATION_ID,
            'page' => 1,
            'limit' => 25,
            'sort' => 'id',
            'order' => 'ASC',
        ]));

        $this->assertTrue($qb->getParameter('successful')->getValue());
        $this->assertSame(RefData::PERMIT_APP_STATUS_AWAITING_FEE, $qb->getParameter('status')->getValue());
    }

    public function testFetchListBindsThePreGrantStatuses(): void
    {
        $qb = $this->createRealQb();
        $this->sut->expects('fetchPaginatedList')->andReturn(['RESULTS']);

        $this->sut->fetchList(GetListByIrhpApplication::create([
            'irhpApplication' => self::IRHP_APPLICATION_ID,
            'page' => 1,
            'limit' => 25,
            'sort' => 'id',
            'order' => 'ASC',
            'isPreGrant' => true,
        ]));

        $this->assertSame(IrhpInterface::PRE_GRANT_STATUSES, $qb->getParameter('status')->getValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('nullCountProvider')]
    public function testFetchCountInRangeWhereApplicationAwaitingFee(?int $count, int $expected): void
    {
        $qb = $this->newRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andReturn($count);
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        $this->assertSame($expected, $this->sut->fetchCountInRangeWhereApplicationAwaitingFee(1));

        $this->assertSame(
            'SELECT count(icp.id) FROM ' . Entity::class . ' icp'
            . ' INNER JOIN icp.irhpPermitApplication ipa INNER JOIN ipa.irhpApplication ia'
            . ' WHERE IDENTITY(icp.irhpPermitRange) = ?1 AND ia.status = ?2',
            $qb->getDQL(),
        );
        $this->assertSame(IrhpInterface::STATUS_AWAITING_FEE, $qb->getParameter(2)->getValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('nullCountProvider')]
    public function testFetchCountInStockWhereApplicationAwaitingFee(?int $count, int $expected): void
    {
        $qb = $this->newRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andReturn($count);
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        $this->assertSame(
            $expected,
            $this->sut->fetchCountInStockWhereApplicationAwaitingFee(22, RefData::EMISSIONS_CATEGORY_EURO5_REF),
        );

        $this->assertSame(
            'SELECT count(icp.id) FROM ' . Entity::class . ' icp'
            . ' INNER JOIN icp.irhpPermitApplication ipa INNER JOIN icp.irhpPermitRange ipr'
            . ' INNER JOIN ipa.irhpApplication ia'
            . ' WHERE IDENTITY(ipr.irhpPermitStock) = ?1 AND ia.status = ?2'
            . ' AND IDENTITY(ipr.emissionsCategory) = ?3',
            $qb->getDQL(),
        );
        $this->assertSame(22, $qb->getParameter(1)->getValue());
        $this->assertSame(RefData::EMISSIONS_CATEGORY_EURO5_REF, $qb->getParameter(3)->getValue());
    }

    /**
     * A null count means no rows, which the repository normalises to zero.
     */
    public static function nullCountProvider(): \Iterator
    {
        yield 'no rows' => [null, 0];
        yield 'some rows' => [42, 42];
    }
}
