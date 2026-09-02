<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\LongText;

use Dvsa\Olcs\Transfer\Query\LongText\ByReferenceKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ByReferenceKey::class)]
final class ByReferenceKeyTest extends TestCase
{
    public function testStructure(): void
    {
        $query = ByReferenceKey::create(['referenceKey' => 'application-declaration-gv79-gb']);

        self::assertSame('application-declaration-gv79-gb', $query->getReferenceKey());
    }
}
