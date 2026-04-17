<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Fee;

use Dvsa\Olcs\Api\Domain\Command\Fee\UpdateFeeStatus;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use PHPUnit\Framework\TestCase;

/**
 * Update Fee Status Test
 */
class UpdateFeeStatusTest extends TestCase
{
    public function testStructure(): void
    {
        $data = [
            'id' => 1,
            'status' => Fee::STATUS_REFUNDED
        ];

        $command = UpdateFeeStatus::create($data);

        $this->assertEquals(1, $command->getId());
        $this->assertEquals(Fee::STATUS_REFUNDED, $command->getStatus());
    }
}
