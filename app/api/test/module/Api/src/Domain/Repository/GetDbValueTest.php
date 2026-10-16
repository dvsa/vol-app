<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\GetDbValue as GetDbValueRepo;
use Dvsa\Olcs\Api\Entity\Application\Application;

final class GetDbValueTest extends RepositoryTestCase
{
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(GetDbValueRepo::class, true);
    }

    public function testFetchOneEntityByX(): void
    {
        $this->sut->setEntity(Application::class);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchOneEntityByX('id', [0 => 'arg']));

        $this->assertSame(
            'SELECT m FROM ' . Application::class . ' m WHERE m.id = :id',
            $qb->getDQL(),
        );
        $this->assertSame('arg', $qb->getParameter('id')->getValue());
    }
}
