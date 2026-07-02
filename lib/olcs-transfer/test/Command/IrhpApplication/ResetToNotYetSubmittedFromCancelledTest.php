<?php

namespace Dvsa\OlcsTest\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\IrhpApplication\ResetToNotYetSubmittedFromCancelled;

/**
 * ResetToNotYetSubmittedFromCancelledTest
 */
class ResetToNotYetSubmittedFromCancelledTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $id = 127;

        $data = ['id' => $id];

        $command = ResetToNotYetSubmittedFromCancelled::create($data);

        $this->assertEquals($id, $command->getId());
    }
}
