<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as Repo;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as Entity;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermitType;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthType;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\Fee\FeeTypeList as FeeTypeListQry;
use Dvsa\Olcs\Transfer\Query\FeeType\GetList as AdminFeeTypeListQry;
use Mockery as m;

final class FeeTypeTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' ft';

    /**
     * withRefdata() joins five associations; ft.feeType is then joined a second time as ftft,
     * which is the alias the list ordering needs. See the migration findings.
     */
    private const string REFDATA_JOINS = ' LEFT JOIN ft.irfoFeeType w0 LEFT JOIN ft.feeType w1'
        . ' LEFT JOIN ft.accrualRule w2 LEFT JOIN ft.licenceType w3 LEFT JOIN ft.goodsOrPsv w4';

    private const string LIST_SELECT = 'SELECT ft, w0, w1, w2, w3, w4, ftft';

    private const string LIST_JOINS = self::REFDATA_JOINS . ' LEFT JOIN ft.feeType ftft';

    /** Every non-admin list ends the same way. */
    private const string LIST_TAIL = ' AND ft.effectiveFrom <= :effectiveFrom'
        . ' ORDER BY ftft.id ASC, ft.effectiveFrom DESC';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * The latest fee type is the most recent one effective on the date, preferring a traffic
     * area specific rate over the fallback with a null traffic area.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('fetchLatestProvider')]
    public function testFetchLatest(?string $trafficArea, string $expectedExtra, string $expectedOrder): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn(['RESULT']);

        $this->assertSame(
            'RESULT',
            $this->sut->fetchLatest(
                new RefData('feeType'),
                new RefData('goodsOrPsv'),
                new RefData('licenceType'),
                new \DateTime('2015-01-01'),
                $trafficArea,
            ),
        );

        $this->assertSame(
            'SELECT ft, w0, w1, w2, w3, w4' . self::FROM . self::REFDATA_JOINS
            . ' WHERE ft.feeType = :feeType AND ft.goodsOrPsv = :goodsOrPsv'
            . ' AND (ft.licenceType = :licenceType OR ft.licenceType IS NULL)'
            . ' AND ft.effectiveFrom <= :effectiveOn'
            . $expectedExtra
            . $expectedOrder,
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getMaxResults());
    }

    public static function fetchLatestProvider(): \Iterator
    {
        yield 'no traffic area' => [
            null,
            ' AND ft.trafficArea IS NULL',
            ' ORDER BY ft.effectiveFrom DESC',
        ];
        // Traffic-area-specific rates sort above the null fallback.
        yield 'a traffic area' => [
            'B',
            ' AND (ft.trafficArea = :trafficArea OR ft.trafficArea IS NULL)',
            ' ORDER BY ft.trafficArea DESC, ft.effectiveFrom DESC',
        ];
    }

    public function testFetchLatestNotFound(): void
    {
        $this->createRealQb()->stubbedQuery()->expects('execute')->andReturn([]);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchLatest(new RefData('feeType'), new RefData('goodsOrPsv'));
    }

    public function testFetchLatestOptionalReturnsNull(): void
    {
        $this->createRealQb()->stubbedQuery()->expects('execute')->andReturn([]);

        $this->assertNull(
            $this->sut->fetchLatest(new RefData('feeType'), new RefData('goodsOrPsv'), null, null, null, true),
        );
    }

    public function testFetchLatestForIrfo(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn(['RESULT']);

        $this->assertSame(
            'RESULT',
            $this->sut->fetchLatestForIrfo(new RefData('irfoFeeType'), new RefData('feeType')),
        );

        $this->assertSame(
            'SELECT ft' . self::FROM
            . ' WHERE ft.feeType = :feeType AND ft.irfoFeeType = :irfoFeeType'
            . ' ORDER BY ft.effectiveFrom DESC',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getMaxResults());
    }

    public function testFetchLatestForOverpayment(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn(['RESULT']);

        $this->assertSame('RESULT', $this->sut->fetchLatestForOverpayment());

        $this->assertSame(
            'SELECT ft' . self::FROM . ' WHERE ft.feeType = :feeType'
            . ' ORDER BY ft.effectiveFrom DESC',
            $qb->getDQL(),
        );
        $this->assertSame(Entity::FEE_TYPE_ADJUSTMENT, $qb->getParameter('feeType')->getValue());
    }

    /**
     * Each list context selects its own set of fee types; the types are inlined into the IN().
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('listProvider')]
    public function testFetchList(array $data, string $expectedWhere): void
    {
        $qb = $this->createRealQb();
        $this->sut->expects('fetchPaginatedList')->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchList(FeeTypeListQry::create($data)));

        $this->assertSame(
            self::LIST_SELECT . self::FROM . self::LIST_JOINS . $expectedWhere . self::LIST_TAIL,
            $qb->getDQL(),
        );
    }

    public static function listProvider(): \Iterator
    {
        $notMisc = ' WHERE ft.isMiscellaneous = :isMiscellaneous';

        yield 'bus registration' => [
            ['busReg' => 1, 'effectiveDate' => '2014-10-26'],
            $notMisc . " AND ft.feeType IN('"
            . Entity::FEE_TYPE_BUSAPP . "', '" . Entity::FEE_TYPE_BUSVAR . "')",
        ];

        yield 'organisation' => [
            ['organisation' => 1, 'effectiveDate' => '2014-10-26'],
            $notMisc . " AND ft.feeType IN('"
            . Entity::FEE_TYPE_IRFOGVPERMIT . "', '" . Entity::FEE_TYPE_IRFOPSVANN
            . "', '" . Entity::FEE_TYPE_IRFOPSVAPP . "', '" . Entity::FEE_TYPE_IRFOPSVCOPY . "')",
        ];

        yield 'miscellaneous' => [
            ['isMiscellaneous' => 'Y', 'effectiveDate' => '2014-10-26'],
            $notMisc,
        ];
    }

    /**
     * The fee-rate admin list is a different query class: it ignores the effective date, shows
     * every visible rate, and is the only one carrying the goodsOrPsv and feeType filters.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('adminListProvider')]
    public function testFetchListForFeeRateAdmin(array $data, string $expectedLeadingWhere): void
    {
        $qb = $this->createRealQb();
        $this->sut->expects('fetchPaginatedList')->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchList(AdminFeeTypeListQry::create($data)));

        $this->assertSame(
            self::LIST_SELECT . self::FROM . self::LIST_JOINS
            . ' WHERE' . $expectedLeadingWhere
            . ' ft.goodsOrPsv IS NOT NULL AND ft.isVisibleInInternal = :isVisibleInInternal'
            . ' ORDER BY ft.id ASC',
            $qb->getDQL(),
        );
    }

    public static function adminListProvider(): \Iterator
    {
        yield 'no filters' => [[], ''];
        yield 'goods or psv' => [['goodsOrPsv' => 'lcat_gv'], ' ft.goodsOrPsv = :goodsOrPsv AND'];
        yield 'fee type' => [['feeType' => 'APP'], ' ft.feeType = :feeType AND'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('irfoEntityProvider')]
    public function testGetLatestIrfoFeeType(callable $makeEntity): void
    {
        $feeTypeFeeType = new RefData('feeTypefeeType');

        $this->sut->expects('fetchLatestForIrfo')->andReturn(['foo']);

        $this->assertSame(['foo'], $this->sut->getLatestIrfoFeeType($makeEntity(), $feeTypeFeeType));
    }

    public static function irfoEntityProvider(): \Iterator
    {
        yield 'gv permit' => [
            static function (): IrfoGvPermit {
                $type = new IrfoGvPermitType();
                $type->setIrfoFeeType(new RefData('feeTypefeeType'));

                return new IrfoGvPermit(new Organisation(), $type, new RefData('status'));
            },
        ];
        yield 'psv auth' => [
            static function (): IrfoPsvAuth {
                $type = new IrfoPsvAuthType();
                $type->setIrfoFeeType(new RefData('feeTypefeeType'));

                return new IrfoPsvAuth(new Organisation(), $type, new RefData('status'));
            },
        ];
    }

    public function testGetLatestIrfoFeeTypeForUnknownEntity(): void
    {
        $this->expectException(NotFoundException::class);

        $this->sut->getLatestIrfoFeeType(new \stdClass(), new RefData('feeTypefeeType'));
    }

    public function testFetchDistinctFeeTypesVisibleInInternal(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchDistinctFeeTypesVisibleInInternal());

        $this->assertSame(
            'SELECT DISTINCT ftft.id' . self::FROM . ' LEFT JOIN ft.feeType ftft'
            . ' WHERE ft.isVisibleInInternal = :isVisibleInInternal'
            . ' ORDER BY ftft.id ASC',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('isVisibleInInternal')->getValue());
    }
}
