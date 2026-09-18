<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Messaging;

use PHPUnit\Framework\TestCase;

class EnableFileUploadTest extends TestCase
{
    public function testStructure()
    {
        $data = [
            'organisation' => 111,
        ];

        $command = \Dvsa\Olcs\Transfer\Command\Messaging\EnableFileUpload::create($data);

        $this->assertEquals(111, $command->getOrganisation());
    }
}
