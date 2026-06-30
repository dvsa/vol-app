<?php

namespace Dvsa\OlcsTest\Transfer\Command\Organisation;

use Dvsa\Olcs\Transfer\Command\Organisation\UpdateBusinessType;

/**
 * Update Business Type test
 */
class UpdateBusinessTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 111,
            'application' => 222,
            'licence' => 333,
            'variation' => 444,
            'version' => 1,
            'businessType' => 'org_t_rc',
            'foo' => 'bar'
        ];

        $command = UpdateBusinessType::create($data);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(222, $command->getApplication());
        $this->assertEquals(333, $command->getLicence());
        $this->assertEquals(444, $command->getVariation());
        $this->assertEquals(1, $command->getVersion());
        $this->assertEquals('org_t_rc', $command->getBusinessType());
    }
}
