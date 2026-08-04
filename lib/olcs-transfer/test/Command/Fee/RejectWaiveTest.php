<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Fee;

use Dvsa\Olcs\Transfer\Command\Fee\RejectWaive;

/**
 * Reject Waive test
 */
final class RejectWaiveTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 111,
            'version' => 1,
        ];

        $command = RejectWaive::create($data);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(1, $command->getVersion());
    }
}
