<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as Repo;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as Entity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Support\TestQueryBuilder;

final class IrhpPermitApplicationTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' ipa';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * The stock's validity and country hang two joins above the permit application, so they are
     * projected alongside it rather than left to lazy loading.
     */
    public function testGetByIrhpApplicationWithStockInfo(): void
    {
        $qb = $this->expectEntityManagerQb();
        $qb->stubbedQuery()->expects('getResult')->withNoArgs()->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->getByIrhpApplicationWithStockInfo(1));

        $this->assertSame(
            'SELECT ipa as irhpPermitApplication, ips.validTo as validTo, ips.id as stockId,'
            . ' IDENTITY(ips.country) as countryId'
            . self::FROM
            . ' INNER JOIN ipa.irhpPermitWindow ipw INNER JOIN ipw.irhpPermitStock ips'
            . ' WHERE IDENTITY(ipa.irhpApplication) = ?1',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter(1)->getValue());
    }

    /**
     * The emissions category selects which column to sum, so it is interpolated into the DQL. Only
     * the two mapped categories are allowed through.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('emissionsCategoryProvider')]
    public function testGetRequiredPermitCountWhereApplicationAwaitingPayment(
        string $emissionsCategoryId,
        string $expectedField,
    ): void {
        $qb = $this->expectEntityManagerQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->withNoArgs()->andReturn(32);

        $this->assertSame(
            32,
            $this->sut->getRequiredPermitCountWhereApplicationAwaitingPayment(5, $emissionsCategoryId),
        );

        $this->assertSame(
            'SELECT sum(ipa.' . $expectedField . ')' . self::FROM
            . ' INNER JOIN ipa.irhpPermitWindow ipw INNER JOIN ipa.irhpApplication ia'
            . ' WHERE IDENTITY(ipw.irhpPermitStock) = ?1 AND ia.status = ?2',
            $qb->getDQL(),
        );
        $this->assertSame(5, $qb->getParameter(1)->getValue());
        $this->assertSame(IrhpInterface::STATUS_AWAITING_FEE, $qb->getParameter(2)->getValue());
    }

    public static function emissionsCategoryProvider(): \Iterator
    {
        yield 'euro 5' => [RefData::EMISSIONS_CATEGORY_EURO5_REF, 'requiredEuro5'];
        yield 'euro 6' => [RefData::EMISSIONS_CATEGORY_EURO6_REF, 'requiredEuro6'];
    }

    /** No rows to sum means no permits required, not an unknown count. */
    public function testGetRequiredPermitCountWithNoMatchingApplications(): void
    {
        $qb = $this->expectEntityManagerQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->withNoArgs()->andReturnNull();

        $this->assertSame(
            0,
            $this->sut->getRequiredPermitCountWhereApplicationAwaitingPayment(
                5,
                RefData::EMISSIONS_CATEGORY_EURO5_REF,
            ),
        );
    }

    public function testGetRequiredPermitCountRejectsAnUnmappedEmissionsCategory(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Emissions category id emissions_cat_nil is not supported');

        $this->sut->getRequiredPermitCountWhereApplicationAwaitingPayment(5, 'emissions_cat_nil');
    }

    private function expectEntityManagerQb(): TestQueryBuilder
    {
        $qb = $this->newRealQb();

        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        return $qb;
    }
}
