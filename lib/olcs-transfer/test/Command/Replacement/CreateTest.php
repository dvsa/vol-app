<?php

namespace Dvsa\OlcsTest\Transfer\Command\Replacement;

use Dvsa\Olcs\Transfer\Command\Replacement\Create;

/**
 * Create test
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [

            'placeholder' => '{{text}}',
            'replacementText' => 'a string'
        ];

        $command = Create::create($data);

        $this->assertEquals($data['placeholder'], $command->getPlaceholder());
        $this->assertEquals($data['replacementText'], $command->getReplacementText());
    }
}
