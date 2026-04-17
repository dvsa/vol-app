<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\CorrespondenceInbox::class)]
class CorrespondenceInboxTest extends RepositoryTestCase
{
    /** @var  Repository\CorrespondenceInbox */
    protected $sut;

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

        $qb->shouldReceive('expr->eq')->with('l.translateToWelsh', 0)->once()->andReturn('condition1');
        $qb->shouldReceive('andWhere')->with('condition1')->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('m.accessed', 0)->once()->andReturn('condition2');
        $qb->shouldReceive('andWhere')->with('condition2')->once()->andReturnSelf();

        $qb->shouldReceive('expr->gte')->with('m.createdOn', ':minDate')->once()->andReturn('condition3');
        $qb->shouldReceive('andWhere')->with('condition3')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('minDate', $minDate)->once()->andReturnSelf();

        $qb->shouldReceive('expr->lte')->with('m.createdOn', ':maxDate')->once()->andReturn('condition4');
        $qb->shouldReceive('andWhere')->with('condition4')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('maxDate', $maxDate)->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('m.printed', 0)->once()->andReturn('condition5');
        $qb->shouldReceive('andWhere')->with('condition5')->once()->andReturnSelf();

        $qb->shouldReceive('expr->isNotNull')->with('l.id')->once()->andReturn('condition6');
        $qb->shouldReceive('andWhere')->with('condition6')->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($qb);

        $mockQry = m::mock(\Doctrine\ORM\AbstractQuery::class);
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

        static::assertEquals('EXPECT', $this->sut->getAllRequiringPrint($minDate, $maxDate));
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

        $qb->shouldReceive('expr->eq')->with('m.accessed', 0)->once()->andReturn('condition1');
        $qb->shouldReceive('andWhere')->with('condition1')->once()->andReturnSelf();

        $qb->shouldReceive('expr->gte')->with('m.createdOn', ':minDate')->once()->andReturn('condition2');
        $qb->shouldReceive('andWhere')->with('condition2')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('minDate', $minDate)->once()->andReturnSelf();

        $qb->shouldReceive('expr->lte')->with('m.createdOn', ':maxDate')->once()->andReturn('condition3');
        $qb->shouldReceive('andWhere')->with('condition3')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('maxDate', $maxDate)->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('m.emailReminderSent', 0)->once()->andReturn('condition4');
        $qb->shouldReceive('andWhere')->with('condition4')->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('m.printed', 0)->once()->andReturn('condition5');
        $qb->shouldReceive('andWhere')->with('condition5')->once()->andReturnSelf();

        $qb->shouldReceive('expr->isNotNull')->with('l.id')->once()->andReturn('condition6');
        $qb->shouldReceive('andWhere')->with('condition6')->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('l.translateToWelsh', 0)->once()->andReturn('condition7');
        $qb->shouldReceive('andWhere')->with('condition7')->once()->andReturnSelf();

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($qb);

        $mockQry = m::mock(\Doctrine\ORM\AbstractQuery::class);
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

        static::assertEquals('EXPECT', $this->sut->getAllRequiringReminder($minDate, $maxDate));
    }

    public function testFetchByDocumentId(): void
    {
        $documentId = 123;

        $qb = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('m')->once()->andReturn($qb);

        $qb->shouldReceive('expr->eq')->with('m.document', ':document')->once()->andReturn('condition');
        $qb->shouldReceive('andWhere')->with('condition')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('document', $documentId)->once()->andReturnSelf();

        $qb->shouldReceive('getQuery->getResult')->once()->andReturn('FOO');
        $this->assertEquals('FOO', $this->sut->fetchByDocumentId($documentId));
    }
}
