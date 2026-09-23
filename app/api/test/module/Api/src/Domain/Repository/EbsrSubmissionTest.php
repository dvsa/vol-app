<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\QueryException;
use Dvsa\Olcs\Api\Domain\Repository\EbsrSubmission as Repo;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as Entity;
use Dvsa\Olcs\Api\Domain\Query\Bus\EbsrSubmissionList;
use Mockery as m;

final class EbsrSubmissionTest extends RepositoryTestCase
{
    private const string JOINS = ' LEFT JOIN m.ebsrSubmissionStatus w0 LEFT JOIN m.ebsrSubmissionType w1'
        . ' LEFT JOIN m.busReg b LEFT JOIN b.licence l'
        . ' LEFT JOIN b.otherServices w2 LEFT JOIN l.organisation w3';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * The only production caller passes the organisation alone, which is the path that works.
     */
    public function testFetchByOrganisation(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByOrganisation('ORG1'));

        $this->assertSame(
            'SELECT m, w0, w1, b, l, w2, w3 FROM ' . Entity::class . ' m' . self::JOINS
            . ' WHERE m.organisation = :organisation',
            $qb->getDQL(),
        );
        $this->assertSame('ORG1', $qb->getParameter('organisation')->getValue());
    }

    /**
     * Pins a latent defect: the submission-status branch filters on 'e.ebsrSubmissionStatus',
     * but this method never joins an 'e' alias (TxcInbox does, which is where it looks copied
     * from). The resulting DQL cannot compile. Unreachable today because the sole caller omits
     * the argument; correcting the alias to 'm' will fail this test, which is the intent.
     */
    public function testFetchByOrganisationWithAStatusBuildsUncompilableDql(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->shouldReceive('getResult')->andReturn([]);

        $this->sut->fetchByOrganisation('ORG1', 'submission_type', 'submission_status');

        $this->assertStringContainsString('AND e.ebsrSubmissionStatus = :ebsrSubmissionStatus', $qb->getDQL());

        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches("/'e' is not defined/");

        $this->compileDql($qb->getDQL());
    }

    public function testFetchForOrganisationByStatus(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(1)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchForOrganisationByStatus(3, 'status', 1));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m'
            . ' WHERE m.ebsrSubmissionStatus = :ebsrSubmissionStatus AND m.organisation = :organisation',
            $qb->getDQL(),
        );
        $this->assertSame('status', $qb->getParameter('ebsrSubmissionStatus')->getValue());
        $this->assertSame(3, $qb->getParameter('organisation')->getValue());
    }

    public function testBuildDefaultListQuery(): void
    {
        $qb = $this->createRealQb();

        $this->sut->buildDefaultListQuery($qb, m::mock(\Dvsa\Olcs\Transfer\Query\QueryInterface::class));

        $this->assertSame(
            'SELECT m, w0, w1, b, l, w2, w3 FROM ' . Entity::class . ' m' . self::JOINS,
            $qb->getDQL(),
        );
    }

    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $query = EbsrSubmissionList::create(['organisation' => 3, 'subType' => 'bar', 'status' => 'foo']);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m'
            . ' WHERE m.organisation = :organisation'
            . ' AND m.ebsrSubmissionStatus IN(:ebsrSubmissionStatus)'
            . ' AND m.ebsrSubmissionType = :ebsrSubmissionType'
            . ' AND m.ebsrSubmissionStatus <> :ebsrtSubmissionStatus',
            $qb->getDQL(),
        );
        $this->assertSame('foo', $qb->getParameter('ebsrSubmissionStatus')->getValue());
        $this->assertSame('bar', $qb->getParameter('ebsrSubmissionType')->getValue());
        $this->assertSame(Entity::UPLOADED_STATUS, $qb->getParameter('ebsrtSubmissionStatus')->getValue());
    }
}
