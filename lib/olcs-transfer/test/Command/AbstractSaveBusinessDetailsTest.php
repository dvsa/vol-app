<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command;

use Dvsa\Olcs\Transfer\Command\AbstractSaveBusinessDetails;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Command\AbstractSaveBusinessDetails::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Command\Licence\UpdateBusinessDetails::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Command\Application\UpdateBusinessDetails::class)]
final class AbstractSaveBusinessDetailsTest extends MockeryTestCase
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

        $this->assertEquals(111, $sut->getId());
        $this->assertEquals(2, $sut->getVersion());
        $this->assertEquals('myname', $sut->getName());
        $this->assertEquals('mynob', $sut->getNatureOfBusiness());
        $this->assertEquals('mynumber', $sut->getCompanyOrLlpNo());
        $this->assertEquals('myaddress', $sut->getRegisteredAddress());
        $this->assertEquals(['mytradingnames'], $sut->getTradingNames());
        $this->assertEquals(false, $sut->getPartial());
        $this->assertEquals('unit_AllowEmail', $sut->getAllowEmail());
    }
}
