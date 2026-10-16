<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\IrfoGvPermit as Repo;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit as Entity;

final class IrfoGvPermitTest extends RepositoryTestCase
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

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.organisation = :organisation',
            $qb->getDQL(),
        );
        $this->assertSame('ORG1', $qb->getParameter('organisation')->getValue());
    }
}
