<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\Note as Repo;
use Dvsa\Olcs\Api\Entity\Note\Note as Entity;

final class NoteTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class);
    }

    public function testFetchByOrganisation(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByOrganisation('ORG1'));

        // The parameter name carries a typo in the repository (:organisaion); harmless, but
        // the old double could not show it because it recorded values, not placeholders.
        $this->assertSame(
            'SELECT n FROM ' . Entity::class . ' n WHERE n.organisation = :organisaion',
            $qb->getDQL(),
        );
        $this->assertSame('ORG1', $qb->getParameter('organisaion')->getValue());
    }

    public function testFetchByTransportManager(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByTransportManager('TM1'));

        $this->assertSame(
            'SELECT n FROM ' . Entity::class . ' n WHERE n.transportManager = :transportManager',
            $qb->getDQL(),
        );
        $this->assertSame('TM1', $qb->getParameter('transportManager')->getValue());
    }

    public function testFetchForOverview(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULT']);

        $this->assertSame('RESULT', $this->sut->fetchForOverview(7, 1, 2, Entity::NOTE_TYPE_CASE));

        $this->assertSame(
            'SELECT n FROM ' . Entity::class . ' n'
            . ' WHERE n.application = :applicationId AND n.licence = :licenceId'
            . ' AND n.transportManager = :tmId AND n.noteType = :noteTypeId'
            . ' ORDER BY n.priority DESC, n.createdOn DESC',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('applicationId')->getValue());
        $this->assertSame(7, $qb->getParameter('licenceId')->getValue());
        $this->assertSame(2, $qb->getParameter('tmId')->getValue());
        $this->assertSame(Entity::NOTE_TYPE_CASE, $qb->getParameter('noteTypeId')->getValue());
        $this->assertSame(1, $qb->getMaxResults());
    }
}
