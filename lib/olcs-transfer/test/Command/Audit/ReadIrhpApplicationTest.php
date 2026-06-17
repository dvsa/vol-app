<?php

namespace Dvsa\OlcsTest\Transfer\Command\Audit;

use Dvsa\Olcs\Transfer\Command\Audit\ReadIrhpApplication;

/**
 * ReadIrhpApplicationTest
 */
class ReadIrhpApplicationTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 2,
        ];

        $command = ReadIrhpApplication::create($data);

        static::assertEquals(
            [
                'id' => 2,
            ],
            $command->getArrayCopy()
        );
    }
}
