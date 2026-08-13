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

        $condition1 = $this->mockExprEq('l.translateToWelsh', 0);
        $qb->shouldReceive('expr->eq')->with('l.translateToWelsh', 0)->once()->andReturn($condition1);
        $qb->shouldReceive('andWhere')->with($condition1)->once()->andReturnSelf();

        $condition2 = $this->mockExprEq('m.accessed', 0);
        $qb->shouldReceive('expr->eq')->with('m.accessed', 0)->once()->andReturn($condition2);
        $qb->shouldReceive('andWhere')->with($condition2)->once()->andReturnSelf();

        $condition3 = $this->mockExprGte('m.createdOn', ':minDate');
        $qb->shouldReceive('expr->gte')->with('m.createdOn', ':minDate')->once()->andReturn($condition3);
        $qb->shouldReceive('andWhere')->with($condition3)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('minDate', $minDate)->once()->andReturnSelf();

        $condition4 = $this->mockExprLte('m.createdOn', ':maxDate');
        $qb->shouldReceive('expr->lte')->with('m.createdOn', ':maxDate')->once()->andReturn($condition4);
        $qb->shouldReceive('andWhere')->with($condition4)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('maxDate', $maxDate)->once()->andReturnSelf();

        $condition5 = $this->mockExprEq('m.printed', 0);
        $qb->shouldReceive('expr->eq')->with('m.printed', 0)->once()->andReturn($condition5);
        $qb->shouldReceive('andWhere')->with($condition5)->once()->andReturnSelf();

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

        $condition1 = $this->mockExprEq('m.accessed', 0);
        $qb->shouldReceive('expr->eq')->with('m.accessed', 0)->once()->andReturn($condition1);
        $qb->shouldReceive('andWhere')->with($condition1)->once()->andReturnSelf();

        $condition2 = $this->mockExprGte('m.createdOn', ':minDate');
        $qb->shouldReceive('expr->gte')->with('m.createdOn', ':minDate')->once()->andReturn($condition2);
        $qb->shouldReceive('andWhere')->with($condition2)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('minDate', $minDate)->once()->andReturnSelf();

        $condition3 = $this->mockExprLte('m.createdOn', ':maxDate');
        $qb->shouldReceive('expr->lte')->with('m.createdOn', ':maxDate')->once()->andReturn($condition3);
        $qb->shouldReceive('andWhere')->with($condition3)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('maxDate', $maxDate)->once()->andReturnSelf();

        $condition4 = $this->mockExprEq('m.emailReminderSent', 0);
        $qb->shouldReceive('expr->eq')->with('m.emailReminderSent', 0)->once()->andReturn($condition4);
        $qb->shouldReceive('andWhere')->with($condition4)->once()->andReturnSelf();

        $condition5 = $this->mockExprEq('m.printed', 0);
        $qb->shouldReceive('expr->eq')->with('m.printed', 0)->once()->andReturn($condition5);
        $qb->shouldReceive('andWhere')->with($condition5)->once()->andReturnSelf();

        $qb->shouldReceive('expr->isNotNull')->with('l.id')->once()->andReturn('condition6');
        $qb->shouldReceive('andWhere')->with('condition6')->once()->andReturnSelf();

        $condition7 = $this->mockExprEq('l.translateToWelsh', 0);
        $qb->shouldReceive('expr->eq')->with('l.translateToWelsh', 0)->once()->andReturn($condition7);
        $qb->shouldReceive('andWhere')->with($condition7)->once()->andReturnSelf();

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

        $condition = $this->mockExprEq('m.document', ':document');
        $qb->shouldReceive('expr->eq')->with('m.document', ':document')->once()->andReturn($condition);
        $qb->shouldReceive('andWhere')->with($condition)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('document', $documentId)->once()->andReturnSelf();

        $qb->shouldReceive('getQuery->getResult')->once()->andReturn('FOO');
        $this->assertEquals('FOO', $this->sut->fetchByDocumentId($documentId));
    }
}
