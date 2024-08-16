<?php

namespace Dvsa\OlcsTest\Api\Entity\View;

use Dvsa\Olcs\Api\Entity\View\VehicleHistoryView;

/**
 * @covers \Dvsa\Olcs\Api\Entity\View\VehicleHistoryView
 */
class VehicleHistoryViewTest extends \PHPUnit\Framework\TestCase
{
    /** @var VehicleHistoryView */
    protected $sut;

    /**  @var array */
    protected $testData;

    public function setUp(): void
    {
        $this->testData = [
            'id' => 'unit_Id',
            'vrm' => 'unit_vrm',
            'licenceNo' => 'unit_LicNo',
            'specifiedDate' => 'unit_specifiedDate',
            'ceasedDate' => 'unit_ceasedDate',
            'discNo' => 'unit_discNo',
            'removalDate' => 'unit_removalDate',
            'issuedDate' => 'unit_issuedDate',
        ];
        $this->sut = new VehicleHistoryView();
    }

    public function testSetGetters()
    {
        $ref = new \ReflectionObject($this->sut);

        // test all teh getters
        foreach ($this->testData as $property => $value) {
            $methodName = ucfirst($property);

            if (!method_exists($this->sut, 'set' . $methodName)) {
                $refProperty = $ref->getProperty($property);
                $refProperty->setAccessible(true);
                $refProperty->setValue($this->sut, $value);
            } else {
                $this->sut->{'set' . $methodName}($value);
            }

            static::assertEquals($value, $this->sut->{'get' . $methodName}());
        }
    }
}
