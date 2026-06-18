<?php

namespace Dvsa\OlcsTest\Transfer\Command;

use Dvsa\Olcs\Transfer\Command\AbstractSaveBusinessDetails;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Dvsa\Olcs\Transfer\Command\AbstractSaveBusinessDetails
 * @covers \Dvsa\Olcs\Transfer\Command\Licence\UpdateBusinessDetails
 * @covers \Dvsa\Olcs\Transfer\Command\Application\UpdateBusinessDetails
 */
class AbstractSaveBusinessDetailsTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $data = [
            'id' => 111,
            'version' => 2,
            'name' => 'myname',
            'natureOfBusiness' => 'mynob',
            'companyOrLlpNo' => 'mynumber',
            'registeredAddress' => 'myaddress',
            'tradingNames' => ['mytradingnames'],
            'partial' => false,
            'allowEmail' => 'unit_AllowEmail',
        ];

        /** @var AbstractSaveBusinessDetails | m\MockInterface $sut */
        $sut = m::mock(AbstractSaveBusinessDetails::class)->makePartial();
        $sut->exchangeArray($data);

        static::assertEquals(111, $sut->getId());
        static::assertEquals(2, $sut->getVersion());
        static::assertEquals('myname', $sut->getName());
        static::assertEquals('mynob', $sut->getNatureOfBusiness());
        static::assertEquals('mynumber', $sut->getCompanyOrLlpNo());
        static::assertEquals('myaddress', $sut->getRegisteredAddress());
        static::assertEquals(['mytradingnames'], $sut->getTradingNames());
        static::assertEquals(false, $sut->getPartial());
        static::assertEquals('unit_AllowEmail', $sut->getAllowEmail());
    }
}
