<?php

namespace Dvsa\OlcsTest\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\Application\UpdateDeclaration;

/**
 * Update Business Type test
 */
class UpdateDeclarationTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 111,
            'version' => 222,
            'declarationConfirmation' => 'Y',
            'interimRequested' => 'N',
            'interimReason' => 'foobar',
            'signatureType' => 'sig_physical_signature',
        ];

        $command = UpdateDeclaration::create($data);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(222, $command->getVersion());
        $this->assertEquals('Y', $command->getDeclarationConfirmation());
        $this->assertEquals('N', $command->getInterimRequested());
        $this->assertEquals('foobar', $command->getInterimReason());
        $this->assertEquals('sig_physical_signature', $command->getSignatureType());
    }
}
