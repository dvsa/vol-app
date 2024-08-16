<?php

namespace Dvsa\OlcsTest\Api\Entity\View;

use Dvsa\Olcs\Api\Entity\View\BusRegSearchView;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * @covers \Dvsa\Olcs\Api\Entity\View\BusRegSearchView
 */
class BusRegSearchViewTest extends MockeryTestCase
{
    /** @var  BusRegSearchView */
    private $sut;
    /** @var array */
    protected $testData;

    public function setUp(): void
    {
        $this->testData = [
            'id' => 15,
            'date1stReg' => 'unit_Date1stReg',
            'serviceNo' => '46474',
            'regNo' => 'PD2737280/15711',
            'licId' => '110',
            'licNo' => 'PD2737280',
            'licStatus' => 'Not Yet Submitted',
            'organisationName' => 'Leeds city council',
            'startPoint' => 'Leeds',
            'finishPoint' => 'Doncaster',
            'busRegStatus' => 'bus_s_reg',
            'busRegStatusDesc' => 'Registered',
            'routeNo' => '15711',
            'variationNo' => '6',
            'localAuthorityId' => 'unit_LaId',
            'isShortNotice' => 'Y',
            'isTxcApp' => true,
        ];
        $this->sut = new BusRegSearchView();
    }

    public function testGetters()
    {
        // test all teh getters
        foreach ($this->testData as $property => $value) {
            $methodName = ucfirst($property);

            $this->sut->{'set' . $methodName}($value);

            static::assertEquals($value, $this->sut->{'get' . $methodName}());
        }
    }
}
