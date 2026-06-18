<?php

namespace Dvsa\OlcsTest\Transfer\Command\Licence;

use Dvsa\Olcs\Transfer\Command\Licence\Overview;

/**
 * Overview test
 */
class OverviewTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 111,
            'version' => 222,
            'leadTcArea' => 'B',
            'reviewDate' => '2015-06-10',
            'expiryDate' => '2016-01-02',
            'translateToWelsh' => 'Y',
        ];

        $command = Overview::create($data);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(222, $command->getVersion());
        $this->assertEquals('B', $command->getLeadTcArea());
        $this->assertEquals('2015-06-10', $command->getReviewDate());
        $this->assertEquals('2016-01-02', $command->getExpiryDate());
        $this->assertEquals('Y', $command->getTranslateToWelsh());
    }
}
