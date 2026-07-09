<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\ContactDetail\PhoneContact;

use Dvsa\Olcs\Transfer\Command\ContactDetail\PhoneContact\Delete;

/**
 * @covers Dvsa\Olcs\Transfer\Command\ContactDetail\PhoneContact\Delete
 */
final class DeleteTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $id = 9999;

        /** @var Delete $command */
        $command = Delete::create(['id' => $id]);

        $this->assertEquals($id, $command->getId());
    }
}
