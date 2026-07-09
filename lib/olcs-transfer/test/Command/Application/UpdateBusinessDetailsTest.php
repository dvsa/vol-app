<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\Application\UpdateBusinessDetails;

/**
 * @covers Dvsa\Olcs\Transfer\Command\Application\UpdateBusinessDetails
 */
final class UpdateBusinessDetailsTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'licence' => 7777,
        ];

        $command = UpdateBusinessDetails::create($data);

        $this->assertEquals(7777, $command->getLicence());
    }
}
