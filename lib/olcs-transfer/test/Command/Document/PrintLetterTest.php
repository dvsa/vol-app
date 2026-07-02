<?php

namespace Dvsa\OlcsTest\Transfer\Command\Document;

use Dvsa\Olcs\Transfer\Command\Document\PrintLetter;

/**
 * @covers \Dvsa\Olcs\Transfer\Command\Document\PrintLetter
 */
class PrintLetterTest extends \PHPUnit\Framework\TestCase
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

        static::assertEquals('unit_id', $command->getId());
        static::assertEquals('unit_method', $command->getMethod());
        static::assertEquals(true, $command->getForceCorrespondence());
    }
}
