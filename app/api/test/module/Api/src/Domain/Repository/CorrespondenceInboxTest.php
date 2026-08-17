<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\CorrespondenceInbox::class)]
final class CorrespondenceInboxTest extends RepositoryTestCase
{
    /** @var  Repository\CorrespondenceInbox */
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpSut(Repository\CorrespondenceInbox::class);
    }

    public function testGetAllRequiringPrint(): void
    {
        $minDate = '2015-01-01';
        $maxDate = '2016-01-01';

        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('addSelect')->with('d, l')->once()->andReturnSelf();
        $qb->shouldReceive('join')->with('m.document', 'd')->once()->andReturnSelf();
        $qb->shouldReceive('join')->with('m.licence', 'l')->once()->andReturnSelf();

        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf();

        $conditionEq1 = $this->mockExprEq('l.translateToWelsh', 0);
        $qb->shouldReceive('expr->eq')->with('l.translateToWelsh', 0)->once()->andReturn($conditionEq1);
        $qb->shouldReceive('andWhere')->with($conditionEq1)->once()->andReturnSelf();

        $conditionEq2 = $this->mockExprEq('m.accessed', 0);
        $qb->shouldReceive('expr->eq')->with('m.accessed', 0)->once()->andReturn($conditionEq2);
        $qb->shouldReceive('andWhere')->with($conditionEq2)->once()->andReturnSelf();

        $conditionGte1 = $this->mockExprGte('m.createdOn', ':minDate');
        $qb->shouldReceive('expr->gte')->with('m.createdOn', ':minDate')->once()->andReturn($conditionGte1);
        $qb->shouldReceive('andWhere')->with($conditionGte1)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('minDate', $minDate)->once()->andReturnSelf();

        $conditionLte1 = $this->mockExprLte('m.createdOn', ':maxDate');
        $qb->shouldReceive('expr->lte')->with('m.createdOn', ':maxDate')->once()->andReturn($conditionLte1);
        $qb->shouldReceive('andWhere')->with($conditionLte1)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('maxDate', $maxDate)->once()->andReturnSelf();

        $conditionEq3 = $this->mockExprEq('m.printed', 0);
        $qb->shouldReceive('expr->eq')->with('m.printed', 0)->once()->andReturn($conditionEq3);
        $qb->shouldReceive('andWhere')->with($conditionEq3)->once()->andReturnSelf();

        $qb->shouldReceive('expr->isNotNull')->with('l.id')->once()->andReturn('condition6');
        $qb->shouldReceive('andWhere')->with('condition6')->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($qb);

        $mockQry = m::mock(\Doctrine\ORM\Query::class);
        $mockQry->shouldReceive('setFetchMode')
            ->once()
            ->with(
                Entity\Organisation\CorrespondenceInbox::class,
                'document',
                \Doctrine\ORM\Mapping\ClassMetadata::FETCH_EAGER
            )
            ->andReturnSelf();
        $mockQry->shouldReceive('getResult')->once()->andReturn('EXPECT');

        $qb->shouldReceive('getQuery')->once()->andReturn($mockQry);

        $this->assertEquals('EXPECT', $this->sut->getAllRequiringPrint($minDate, $maxDate));
    }

    public function testGetAllRequiringReminder(): void
    {
        $minDate = '2015-01-01';
        $maxDate = '2016-01-01';

        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('addSelect')->with('d, l, lo, lou, louu, louucd')->once()->andReturnSelf();
        $qb->shouldReceive('join')->with('m.document', 'd')->once()->andReturnSelf();
        $qb->shouldReceive('join')->with('m.licence', 'l')->once()->andReturnSelf();
        $qb->shouldReceive('join')->with('l.organisation', 'lo')->once()->andReturnSelf();
        $qb->shouldReceive('join')->with('lo.organisationUsers', 'lou')->once()->andReturnSelf();
        $qb->shouldReceive('join')->with('lou.user', 'louu')->once()->andReturnSelf();
        $qb->shouldReceive('join')->with('louu.contactDetails', 'louucd')->once()->andReturnSelf();

        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('d.continuationDetails', 'cd')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('cd.checklistDocument', 'cdd')->once()->andReturnSelf();

        $conditionEq4 = $this->mockExprEq('m.accessed', 0);
        $qb->shouldReceive('expr->eq')->with('m.accessed', 0)->once()->andReturn($conditionEq4);
        $qb->shouldReceive('andWhere')->with($conditionEq4)->once()->andReturnSelf();

        $conditionGte2 = $this->mockExprGte('m.createdOn', ':minDate');
        $qb->shouldReceive('expr->gte')->with('m.createdOn', ':minDate')->once()->andReturn($conditionGte2);
        $qb->shouldReceive('andWhere')->with($conditionGte2)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('minDate', $minDate)->once()->andReturnSelf();

        $conditionLte2 = $this->mockExprLte('m.createdOn', ':maxDate');
        $qb->shouldReceive('expr->lte')->with('m.createdOn', ':maxDate')->once()->andReturn($conditionLte2);
        $qb->shouldReceive('andWhere')->with($conditionLte2)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('maxDate', $maxDate)->once()->andReturnSelf();

        $conditionEq5 = $this->mockExprEq('m.emailReminderSent', 0);
        $qb->shouldReceive('expr->eq')->with('m.emailReminderSent', 0)->once()->andReturn($conditionEq5);
        $qb->shouldReceive('andWhere')->with($conditionEq5)->once()->andReturnSelf();

        $conditionEq6 = $this->mockExprEq('m.printed', 0);
        $qb->shouldReceive('expr->eq')->with('m.printed', 0)->once()->andReturn($conditionEq6);
        $qb->shouldReceive('andWhere')->with($conditionEq6)->once()->andReturnSelf();

        $qb->shouldReceive('expr->isNotNull')->with('l.id')->once()->andReturn('condition6');
        $qb->shouldReceive('andWhere')->with('condition6')->once()->andReturnSelf();

        $conditionEq7 = $this->mockExprEq('l.translateToWelsh', 0);
        $qb->shouldReceive('expr->eq')->with('l.translateToWelsh', 0)->once()->andReturn($conditionEq7);
        $qb->shouldReceive('andWhere')->with($conditionEq7)->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($qb);

        $mockQry = m::mock(\Doctrine\ORM\Query::class);
        $mockQry->shouldReceive('setFetchMode')
            ->once()
            ->with(
                Entity\Organisation\CorrespondenceInbox::class,
                'document',
                \Doctrine\ORM\Mapping\ClassMetadata::FETCH_EAGER
            )
            ->andReturnSelf();
        $mockQry->shouldReceive('getResult')->once()->andReturn('EXPECT');

        $qb->shouldReceive('getQuery')->once()->andReturn($mockQry);

        $this->assertEquals('EXPECT', $this->sut->getAllRequiringReminder($minDate, $maxDate));
    }

    public function testFetchByDocumentId(): void
    {
        $documentId = 123;

        $qb = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($qb);

        $conditionEq8 = $this->mockExprEq('m.document', ':document');
        $qb->shouldReceive('expr->eq')->with('m.document', ':document')->once()->andReturn($conditionEq8);
        $qb->shouldReceive('andWhere')->with($conditionEq8)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('document', $documentId)->once()->andReturnSelf();

        $qb->shouldReceive('getQuery->getResult')->once()->andReturn('FOO');
        $this->assertEquals('FOO', $this->sut->fetchByDocumentId($documentId));
    }
}
