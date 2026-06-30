<?php

namespace Dvsa\OlcsTest\Transfer\Command\Replacement;

use Dvsa\Olcs\Transfer\Command\Replacement\Update;

/**
 * Update test
 */
class UpdateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 22,
            'placeholder' => '{{text}}',
            'replacementText' => 'a string'
        ];

        $command = Update::create($data);

        $this->assertEquals($data['id'], $command->getId());
        $this->assertEquals($data['placeholder'], $command->getPlaceholder());
        $this->assertEquals($data['replacementText'], $command->getReplacementText());
    }
}
