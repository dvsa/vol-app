<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Repository\ViOpView as Repo;
use Dvsa\Olcs\Api\Entity\View\ViOpView as Entity;

final class ViOpViewTest extends RepositoryTestCase
{
    private const string PROCEDURE = 'ViStoredProcedures\\ViOpComplete';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * The export is a scalar projection of the pre-rendered VI line plus the ids the caller needs
     * to clear the indicators afterwards.
     */
    public function testFetchForExport(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['result']);

        $this->assertSame(['result'], $this->sut->fetchForExport());

        $this->assertSame(
            'SELECT m.viLine as line, m.licId FROM ' . Entity::class . ' m',
            $qb->getDQL(),
        );
    }

    /** One stored-procedure call per record: the ids are not batched. */
    public function testclearLicencesViIndicators(): void
    {
        $this->expectQueryWithData(self::PROCEDURE, ['licenceId' => 1]);

        $this->sut->clearLicencesViIndicators([['licId' => 1]]);
    }

    /** Any failure is reported as one message; the individual cause is not surfaced. */
    public function testclearLicencesViIndicatorsException(): void
    {
        $this->dbQueryService->shouldReceive('get')
            ->with(self::PROCEDURE)
            ->andThrow(new RuntimeException('foo'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error clearing VI flags for Operating Centres');

        $this->sut->clearLicencesViIndicators([['licId' => 1]]);
    }
}
