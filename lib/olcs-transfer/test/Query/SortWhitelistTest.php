<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\OlcsTest\Transfer\Query\Stub\OrderedOptionalQueryStub;
use Dvsa\OlcsTest\Transfer\Query\Stub\OrderedQueryStub;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The sort whitelist is set on the server, so a client must not be able to supply one in its request.
 *
 * Both ordered traits mark it with @Transfer\DoNotExchange, which AbstractQuery::exchangeArray() honours.
 * That marker is read from the property's docblock as text rather than through the annotation builder,
 * so a change to how DTO rules are declared can switch it off without any other test noticing.
 */
#[CoversClass(AbstractQuery::class)]
final class SortWhitelistTest extends TestCase
{
    /**
     * @param class-string<OrderedQueryStub|OrderedOptionalQueryStub> $queryClass
     */
    #[DataProvider('orderedQueryProvider')]
    public function testCreateIgnoresSortWhitelistFromRequest(string $queryClass): void
    {
        $query = $queryClass::create(['sort' => 'name', 'order' => 'ASC', 'sortWhitelist' => ['name']]);

        $this->assertSame([], $query->getSortWhitelist());
        $this->assertSame('name', $query->getSort());
        $this->assertSame('ASC', $query->getOrder());
    }

    /**
     * @param class-string<OrderedQueryStub|OrderedOptionalQueryStub> $queryClass
     */
    #[DataProvider('orderedQueryProvider')]
    public function testExchangeArrayKeepsServerSortWhitelist(string $queryClass): void
    {
        $query = $queryClass::create([]);
        $query->setSortWhitelist(['id']);

        $query->exchangeArray(['sort' => 'name', 'sortWhitelist' => ['name']]);

        $this->assertSame(['id'], $query->getSortWhitelist());
        $this->assertSame('name', $query->getSort());
        $this->assertFalse($query->isSortWhitelisted());
    }

    /**
     * @return array<string, array{class-string<OrderedQueryStub|OrderedOptionalQueryStub>}>
     */
    public static function orderedQueryProvider(): array
    {
        return [
            'OrderedTrait' => [OrderedQueryStub::class],
            'OrderedTraitOptional' => [OrderedOptionalQueryStub::class],
        ];
    }
}
