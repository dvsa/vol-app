<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query as TransferQry;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\DataService::class)]
final class DataServiceTest extends RepositoryTestCase
{
    public const int ORG_ID = 9001;

    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repository\DataService::class);
    }

    public function testFetchByOrgAndStatusForActiveLicences(): void
    {
        // fetchApplicationStatus() sets $entity itself, so root the builder explicitly.
        $qb = $this->createRealQb(Entity\System\RefData::class, 'm');
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn('EXPECT');

        $query = TransferQry\Application\GetList::create(['organisation' => self::ORG_ID]);

        $this->assertSame('EXPECT', $this->sut->fetchApplicationStatus($query));

        $this->assertSame(
            'SELECT m FROM ' . Entity\System\RefData::class . ' m'
            . ' INNER JOIN ' . Entity\Application\Application::class . ' a WITH a.status = m.id'
            . ' INNER JOIN a.licence l WITH l.organisation = :ORG_ID',
            $qb->getDQL(),
        );
        $this->assertSame(self::ORG_ID, $qb->getParameter('ORG_ID')->getValue());
    }
}
