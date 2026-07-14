<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Fee;

use Dvsa\Olcs\Transfer\Command\Fee\RecommendWaive;

/**
 * Recommend Waive test
 */
final class RecommendWaiveTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 111,
            'version' => 1,
            'waiveReason' => 'foo',
        ];

        $command = RecommendWaive::create($data);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(1, $command->getVersion());
        $this->assertEquals('foo', $command->getWaiveReason());
    }
}
