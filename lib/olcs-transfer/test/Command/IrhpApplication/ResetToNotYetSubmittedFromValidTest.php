<?php

namespace Dvsa\OlcsTest\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\IrhpApplication\ResetToNotYetSubmittedFromValid;

/**
 * ResetToNotYetSubmittedFromValidTest
 */
class ResetToNotYetSubmittedFromValidTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = ['id' => 114];

        $command = ResetToNotYetSubmittedFromValid::create($data);

        $this->assertEquals(114, $command->getId());
    }
}
