<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\Transaction as TransactionRepo;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Transaction as Entity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\Transaction::class)]
final class TransactionTest extends RepositoryTestCase
{
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(TransactionRepo::class);
    }

    public function testFetchByReference(): void
    {
        $ref = 'OLCS-1234-ABCD';
        $result = m::mock(Entity::class);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn([$result]);
        $this->em->expects('lock')->with($result, LockMode::OPTIMISTIC, 1);

        $this->assertSame($result, $this->sut->fetchByReference($ref, Query::HYDRATE_OBJECT, 1));

        $this->assertSame(
            'SELECT t, w0, w1, w2, ft, f, l, w3, w4 FROM ' . Entity::class . ' t'
            . ' LEFT JOIN t.status w0 LEFT JOIN t.type w1 LEFT JOIN t.paymentMethod w2'
            . ' LEFT JOIN t.feeTransactions ft LEFT JOIN ft.fee f LEFT JOIN f.licence l'
            . ' LEFT JOIN f.application w3 LEFT JOIN l.organisation w4'
            . ' WHERE t.reference = :reference',
            $qb->getDQL(),
        );
        $this->assertSame($ref, $qb->getParameter('reference')->getValue());
    }

    public function testFetchOutstandingCardPayments(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->em->shouldReceive('getReference')->andReturnUsing(
            function ($class, $id) {
                $reference = m::mock(RefData::class);
                $reference->shouldReceive('getId')->andReturn($id);

                return $reference;
            }
        );

        $this->assertSame(['RESULTS'], $this->sut->fetchOutstandingCardPayments(60));

        $this->assertSame(
            'SELECT t FROM ' . Entity::class . ' t'
            . ' WHERE t.type = :transactionType AND t.status = :status'
            . ' AND t.paymentMethod IN(:paymentMethods) AND t.createdOn < :maxCreatedOn',
            $qb->getDQL(),
        );
        $this->assertSame(Entity::TYPE_PAYMENT, $qb->getParameter('transactionType')->getValue()->getId());
        $this->assertSame(Entity::STATUS_OUTSTANDING, $qb->getParameter('status')->getValue()->getId());
        $this->assertSame(
            [FeeEntity::METHOD_CARD_ONLINE, FeeEntity::METHOD_CARD_OFFLINE],
            $qb->getParameter('paymentMethods')->getValue(),
        );
        $this->assertInstanceOf(\DateTimeInterface::class, $qb->getParameter('maxCreatedOn')->getValue());
    }
}
