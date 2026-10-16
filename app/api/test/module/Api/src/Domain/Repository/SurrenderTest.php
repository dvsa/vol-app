<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\Surrender;
use Dvsa\Olcs\Api\Entity\Surrender as Entity;

final class SurrenderTest extends RepositoryTestCase
{
    /** @var Surrender */
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Surrender::class);
    }

    public function testFetchByLicenceId(): void
    {
        $licenceId = 1;

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['Result']);

        $this->assertSame(['Result'], $this->sut->fetchByLicenceId($licenceId));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.licence = ' . $licenceId,
            $qb->getDQL(),
        );
    }
}
