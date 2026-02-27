<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Variation;

use Dvsa\Olcs\Api\Domain\Command\Variation\ResetVariation;
use PHPUnit\Framework\TestCase;

/**
 * ResetVariation test
 */
class ResetVariationTest extends TestCase
{
    public function testStructure(): void
    {
        $id = 140;
        $confirm = true;

        $data = [
            'id' => $id,
            'confirm' => $confirm,
        ];

        $command = ResetVariation::create($data);
        $this->assertEquals($id, $command->getId());
        $this->assertEquals($confirm, $command->getConfirm());
    }
}
