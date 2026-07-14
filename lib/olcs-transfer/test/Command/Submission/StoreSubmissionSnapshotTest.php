<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Operator;

use Dvsa\Olcs\Transfer\Command\Submission\StoreSubmissionSnapshot as Cmd;

/**
 * StoreSubmissionSnapshotTest
 */
final class StoreSubmissionSnapshotTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 54,
            'html' => 'HTML',
        ];

        $command = Cmd::create($data);

        $this->assertEquals(54, $command->getId());
        $this->assertEquals('HTML', $command->getHtml());
    }
}
