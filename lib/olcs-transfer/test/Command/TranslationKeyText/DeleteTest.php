<?php

namespace Dvsa\OlcsTest\Transfer\Command\TranslationKeyText;

use Dvsa\Olcs\Transfer\Command\TranslationKeyText\Delete;

/**
 * Delete test
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 'STRID'
        ];

        $command = Delete::create($data);

        $this->assertEquals($data['id'], $command->getId());
    }
}
