<?php

namespace Dvsa\OlcsTest\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\Application\UpdateFinancialEvidence;

/**
 * Update Financial Evidence test
 */
class UpdateFinancialEvidenceTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 111,
            'version' => 222,
            'financialEvidenceUploaded' => '1',
        ];

        $command = UpdateFinancialEvidence::create($data);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(222, $command->getVersion());
        $this->assertEquals(1, $command->getFinancialEvidenceUploaded());
    }
}
