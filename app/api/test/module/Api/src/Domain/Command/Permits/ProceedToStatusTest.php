<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Permits;

use Dvsa\Olcs\Api\Domain\Command\Permits\ProceedToStatus;

/**
 * Proceed to status test
 *
 */
final class ProceedToStatusTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $sut = ProceedToStatus::create(
            [
                'ids' => [1, 2, 3],
                'status' => 'TEST',
            ]
        );

        $this->assertEquals([1, 2, 3], $sut->getIds());
        $this->assertEquals('TEST', $sut->getStatus());
        $this->assertEquals([
            'ids' => [1, 2, 3],
            'status' => 'TEST',
        ], $sut->getArrayCopy());
    }
}
