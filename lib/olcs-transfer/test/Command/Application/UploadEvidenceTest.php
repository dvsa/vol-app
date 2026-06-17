<?php

namespace Dvsa\OlcsTest\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\Application\UploadEvidence;

/**
 * Upload Evidence test
 */
class UploadEvidenceTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $oc1 = [
            'aocId' => 1,
            'adPlacedIn' => 'foo',
            'adPlacedDate' => '2016-01-02'
        ];
        $data = [
            'id' => 111,
            'operatingCentres' => [$oc1],
            'financialEvidence' => true,
            'supportingEvidence' => true,
        ];

        $command = UploadEvidence::create($data);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals([$oc1], $command->getOperatingCentres());
        $this->assertTrue($command->getFinancialEvidence());
        $this->assertTrue($command->getSupportingEvidence());
    }
}
