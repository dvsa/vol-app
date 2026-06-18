<?php

namespace Dvsa\OlcsTest\Transfer\Command\TranslationKey;

use Dvsa\Olcs\Transfer\Command\TranslationKey\Delete;

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
