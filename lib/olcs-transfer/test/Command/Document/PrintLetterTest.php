<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Document;

use Dvsa\Olcs\Transfer\Command\Document\PrintLetter;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Command\Document\PrintLetter::class)]
final class PrintLetterTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = PrintLetter::create(
            [
                'id' => 'unit_id',
                'method' => 'unit_method',
                'forceCorrespondence' => true,
            ]
        );

        $this->assertEquals('unit_id', $command->getId());
        $this->assertEquals('unit_method', $command->getMethod());
        $this->assertEquals(true, $command->getForceCorrespondence());
    }
}
