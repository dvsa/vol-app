<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\System;

use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate;

class GenerateSlaTargetDateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $data = [
            'pi' => 10,
            'submission' => 11,
            'proposeToRevoke' => 12,
            'statement' => 13,
        ];
        $command = GenerateSlaTargetDate::create($data);

        $this->assertEquals($data['pi'], $command->getPi());
        $this->assertEquals($data['submission'], $command->getSubmission());
        $this->assertEquals($data['proposeToRevoke'], $command->getProposeToRevoke());
        $this->assertEquals($data['statement'], $command->getStatement());
    }
}
