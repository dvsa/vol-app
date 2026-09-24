<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\ErruRequest as Repo;
use Dvsa\Olcs\Api\Entity\Si\ErruRequest as Entity;

final class ErruRequestTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('existsByWorkflowIdProvider')]
    public function testExistsByWorkflowId(array $result, bool $expected): void
    {
        $qb = $this->createRealQb()->willReturn($result);

        $this->assertSame($expected, $this->sut->existsByWorkflowId('123456'));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.workflowId = :workflowId',
            $qb->getDQL(),
        );
        $this->assertSame('123456', $qb->getParameter('workflowId')->getValue());
        $this->assertSame(1, $qb->getMaxResults());
    }

    public static function existsByWorkflowIdProvider(): \Iterator
    {
        yield 'found' => [['Result'], true];
        yield 'not found' => [[], false];
    }
}
