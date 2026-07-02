<?php

namespace Dvsa\OlcsTest\Transfer\Command\Operator;

use Dvsa\Olcs\Transfer\Command\Operator\CreateUnlicensed as Cmd;

/**
 * Create Unlicensed Operator command test
 */
class CreateUnlicensedTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'name' => 'Foo Ltd.',
            'operatorType' => 'lcat_psv',
            'trafficArea' => 'B',
            'contactDetails' => ['foo'],
            'isExempt' => 'Y'
        ];

        $command = Cmd::create($data);

        $this->assertEquals('Foo Ltd.', $command->getName());
        $this->assertEquals('lcat_psv', $command->getOperatorType());
        $this->assertEquals('B', $command->getTrafficArea());
        $this->assertEquals(['foo'], $command->getContactDetails());
        $this->assertEquals('Y', $command->getIsExempt());
    }
}
