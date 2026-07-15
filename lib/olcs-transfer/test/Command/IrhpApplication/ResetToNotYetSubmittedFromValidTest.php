<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\IrhpApplication\ResetToNotYetSubmittedFromValid;

/**
 * ResetToNotYetSubmittedFromValidTest
 */
final class ResetToNotYetSubmittedFromValidTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = ['id' => 114];

        $command = ResetToNotYetSubmittedFromValid::create($data);

        $this->assertEquals(114, $command->getId());
    }
}
