<?php

namespace Dvsa\OlcsTest\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateFull;

/**
 * Update full test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class UpdateFullTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $id = 456;
        $dateReceived = '2020-09-01';
        $permitsRequired = [
            'permitsRequiredKey1' => 'permitsRequiredValue1',
            'permitsRequiredKey2' => 'permitsRequiredValue2',
        ];
        $declaration = 1;
        $postData = [
            'postDataKey1' => 'postDataValue1',
            'postDataKey2' => 'postDataValue2',
        ];
        $checked = true;
        $corCertificateNumber = 'UKCR21/00562';

        $data = [
            'id' => $id,
            'dateReceived' => $dateReceived,
            'permitsRequired' => $permitsRequired,
            'declaration' => $declaration,
            'postData' => $postData,
            'checked' => $checked,
            'corCertificateNumber' => $corCertificateNumber,
        ];

        $command = UpdateFull::create($data);

        $this->assertEquals($id, $command->getId());
        $this->assertEquals($dateReceived, $command->getDateReceived());
        $this->assertEquals($permitsRequired, $command->getPermitsRequired());
        $this->assertEquals($declaration, $command->getDeclaration());
        $this->assertEquals($postData, $command->getPostData());
        $this->assertEquals($checked, $command->getChecked());
        $this->assertEquals($corCertificateNumber, $command->getCorCertificateNumber());
    }
}
