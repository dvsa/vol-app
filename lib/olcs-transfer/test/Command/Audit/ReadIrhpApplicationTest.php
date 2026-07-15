<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Audit;

use Dvsa\Olcs\Transfer\Command\Audit\ReadIrhpApplication;

/**
 * ReadIrhpApplicationTest
 */
final class ReadIrhpApplicationTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 2,
        ];

        $command = ReadIrhpApplication::create($data);

        $this->assertEquals([
            'id' => 2,
        ], $command->getArrayCopy());
    }
}
