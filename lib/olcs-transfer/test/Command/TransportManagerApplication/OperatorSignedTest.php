<?php

namespace Dvsa\OlcsTest\Transfer\Command\TransportManagerApplication;

use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\OperatorSigned;

/**
 * @covers \Dvsa\Olcs\Transfer\Command\TransportManagerApplication\OperatorSigned
 */
class OperatorSignedTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 999,
            'version' => 888,
        ];

        $command = OperatorSigned::create($data);

        static::assertEquals(999, $command->getId());
        static::assertEquals(888, $command->getVersion());
    }
}
