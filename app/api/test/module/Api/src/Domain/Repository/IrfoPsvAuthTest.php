<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\IrfoPsvAuth as Repo;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth as Entity;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoPsvAuthContinuationList as IrfoPsvAuthContinuationListQry;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoPsvAuthList as IrfoPsvAuthListQry;

final class IrfoPsvAuthTest extends RepositoryTestCase
{
    private const string REFDATA = ' LEFT JOIN m.status w0 LEFT JOIN m.journeyFrequency w1'
        . ' LEFT JOIN m.withdrawnReason w2';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchById(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['result']);

        $this->assertSame('result', $this->sut->fetchById(24));

        $this->assertSame(
            'SELECT m, w0, w1, w2, w3, w4, w5 FROM ' . Entity::class . ' m' . self::REFDATA
            . ' LEFT JOIN m.irfoPsvAuthType w3 LEFT JOIN m.irfoPsvAuthNumbers w4'
            . ' LEFT JOIN m.countrys w5'
            . ' WHERE m.id = :byId',
            $qb->getDQL(),
        );
        $this->assertSame(24, $qb->getParameter('byId')->getValue());
    }

    public function testFetchByOrganisation(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByOrganisation('ORG1'));

        // The parameter name carries a typo in the repository (:organisaion).
        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.organisation = :organisaion',
            $qb->getDQL(),
        );
        $this->assertSame('ORG1', $qb->getParameter('organisaion')->getValue());
    }

    public function testFetchList(): void
    {
        $qb = $this->createRealQb();
        $this->sut->expects('fetchPaginatedList')->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchList(IrfoPsvAuthListQry::create(['organisation' => 12])));

        $this->assertSame(
            'SELECT m, w0, w1, w2, w3 FROM ' . Entity::class . ' m' . self::REFDATA
            . ' LEFT JOIN m.irfoPsvAuthType w3'
            . ' WHERE m.organisation = :byOrganisation',
            $qb->getDQL(),
        );
        $this->assertSame(12, $qb->getParameter('byOrganisation')->getValue());
    }

    /**
     * The continuation list takes a different filter path entirely: a one-month expiry window
     * plus the continuable statuses, restricted to IRFO organisations.
     */
    public function testFetchListForContinuation(): void
    {
        $qb = $this->createRealQb();
        $this->sut->expects('fetchPaginatedList')->andReturn(['RESULTS']);

        $query = IrfoPsvAuthContinuationListQry::create(['year' => 2016, 'month' => 12]);

        $this->assertSame(['RESULTS'], $this->sut->fetchList($query));

        $this->assertSame(
            'SELECT m, w0, w1, w2, o FROM ' . Entity::class . ' m' . self::REFDATA
            . ' LEFT JOIN m.organisation o'
            . " WHERE m.expiryDate >= :expiryFrom AND m.expiryDate < :expiryTo"
            . " AND m.status IN('" . Entity::STATUS_APPROVED . "', '" . Entity::STATUS_GRANTED
            . "', '" . Entity::STATUS_PENDING . "', '" . Entity::STATUS_RENEW . "')"
            . ' AND o.isIrfo = :isIrfo',
            $qb->getDQL(),
        );
        $this->assertEquals(new \DateTime('2016-12-01'), $qb->getParameter('expiryFrom')->getValue());
        $this->assertEquals(new \DateTime('2017-01-01'), $qb->getParameter('expiryTo')->getValue());
        $this->assertTrue($qb->getParameter('isIrfo')->getValue());
    }
}
