<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\LocalAuthority as Repo;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as Entity;

final class LocalAuthorityTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * Both lookups take the names straight from an EBSR submission, so the values are inlined
     * into the IN() rather than bound.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('lookupProvider')]
    public function testLookups(string $method, array $values, string $expectedWhere): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn(['result']);

        $this->assertSame(['result'], $this->sut->{$method}($values));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE ' . $expectedWhere,
            $qb->getDQL(),
        );
    }

    public static function lookupProvider(): \Iterator
    {
        yield 'by TransXChange name' => [
            'fetchByTxcName',
            ['name1', 'name2'],
            "m.txcName IN('name1', 'name2')",
        ];
        yield 'by NaPTAN code' => [
            'fetchByNaptan',
            ['naptan1', 'naptan2'],
            "m.naptanCode IN('naptan1', 'naptan2')",
        ];
    }
}
