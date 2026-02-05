<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\Command\Permits;

use Dvsa\Olcs\Cli\Domain\Command\Permits\GeneratePermits;

/**
 * Generate permits test
 *
 */
class GeneratePermitsTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $sut = GeneratePermits::create(
            [
                'ids' => [1, 2, 3],
                'user' => 456,
            ]
        );

        static::assertEquals([1, 2, 3], $sut->getIds());
        static::assertEquals(456, $sut->getUser());
        static::assertEquals(
            [
                'ids' => [1, 2, 3],
                'user' => 456,
            ],
            $sut->getArrayCopy()
        );
    }
}
