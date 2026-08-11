<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\IrhpCandidatePermit;

use Dvsa\Olcs\Transfer\Command\IrhpCandidatePermit\Delete;

/**
 * Delete test
 */
final class DeleteTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 21,
        ];

        $command = Delete::create($data);

        $this->assertEquals($data['id'], $command->getId());
    }
}
