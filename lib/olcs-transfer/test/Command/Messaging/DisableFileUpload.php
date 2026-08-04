<?php

declare(strict_types=1);

namespace Command\Messaging;

use PHPUnit\Framework\TestCase;

class DisableFileUpload extends TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 111,
        ];

        $command = \Dvsa\Olcs\Transfer\Command\Messaging\DisableFileUpload::create($data);

        $this->assertEquals(111, $command->getOrganisation());
    }
}
