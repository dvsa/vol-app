<?php

namespace Dvsa\OlcsTest\Api\Domain\Command\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\ProcessRequestMap;

/**
 * Process request map Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ProcessRequestMapTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $id = 1;
        $user = 2;
        $scale = 'small';
        $licence = 'licence';
        $regNo = '123/45678';

        $command = ProcessRequestMap::create(
            [
                'id' => $id,
                'user' => $user,
                'scale' => $scale,
                'licence' => $licence,
                'regNo' => $regNo
            ]
        );

        $this->assertEquals($id, $command->getId());
        $this->assertEquals($user, $command->getUser());
        $this->assertEquals($scale, $command->getScale());
        $this->assertEquals($licence, $command->getLicence());
        $this->assertEquals($regNo, $command->getRegNo());
    }
}
