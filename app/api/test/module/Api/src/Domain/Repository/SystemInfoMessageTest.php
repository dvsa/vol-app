<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\System\SystemInfoMessage as Entity;
use Dvsa\Olcs\Transfer\Query\System\InfoMessage\GetListActive as Qry;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\SystemInfoMessage::class)]
final class SystemInfoMessageTest extends RepositoryTestCase
{
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repository\SystemInfoMessage::class);
    }

    public function testListActive(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchListActive(Qry::create(['isInternal' => true])));

        // The partial select() replaces whatever withRefdata() added; SystemInfoMessage has no
        // RefData associations, so there is nothing joined either way.
        $this->assertSame(
            'SELECT partial m.{id, description} FROM ' . Entity::class . ' m'
            . ' WHERE m.isInternal = :IS_INTERNAL AND m.startDate <= :NOW AND m.endDate >= :NOW',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('IS_INTERNAL')->getValue());
        $this->assertInstanceOf(\DateTimeInterface::class, $qb->getParameter('NOW')->getValue());
    }
}
