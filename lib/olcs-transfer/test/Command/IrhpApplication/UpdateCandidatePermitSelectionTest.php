<?php

namespace Dvsa\OlcsTest\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCandidatePermitSelection;
use PHPUnit\Framework\TestCase;

/**
 * UpdateCandidatePermitSelectionTest
 */
class UpdateCandidatePermitSelectionTest extends TestCase
{
    public function testStructure()
    {
        $id = 114;
        $selectedCandidatePermitIds = [20, 40, 60];

        $data = [
            'id' => 114,
            'selectedCandidatePermitIds' => $selectedCandidatePermitIds,
        ];

        $command = UpdateCandidatePermitSelection::create($data);

        $this->assertEquals($id, $command->getId());
        $this->assertEquals($selectedCandidatePermitIds, $command->getSelectedCandidatePermitIds());
    }
}
