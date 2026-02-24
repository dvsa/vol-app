<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Query\Queue;

use Dvsa\Olcs\Api\Domain\Query\Queue\NextItem;

/**
 * NextItem test
 */
class NextItemTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $query = NextItem::create(['includeTypes' => 'foo', 'excludeTypes' => 'bar']);

        $this->assertEquals('foo', $query->getIncludeTypes());
        $this->assertEquals('bar', $query->getExcludeTypes());
    }
}
