<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\TransportManagerApplication;

use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\OperatorSigned;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Command\TransportManagerApplication\OperatorSigned::class)]
final class OperatorSignedTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 999,
            'version' => 888,
        ];

        $command = OperatorSigned::create($data);

        $this->assertEquals(999, $command->getId());
        $this->assertEquals(888, $command->getVersion());
    }
}
