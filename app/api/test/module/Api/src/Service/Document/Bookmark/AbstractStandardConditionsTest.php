<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query as DomainQry;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Document\Bookmark\AbstractStandardConditions;
use Dvsa\Olcs\Transfer\FieldType\IdentityInterface;
use Dvsa\OlcsTest\Api\Service\Document\Bookmark\Stub\AbstractStandardConditionsStub;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Dvsa\Olcs\Api\Service\Document\Bookmark\AbstractStandardConditions
 */
class AbstractStandardConditionsTest extends MockeryTestCase
{
    /**
     * @dataProvider dpTestGetQuery
     */
    public function testGetQuery($service, $expectClass)
    {
        eval(
            'namespace test\\' . $service . ';' .
            'class TestClass extends \\' . AbstractStandardConditions::class . ' {' .
            "    const SERVICE = '{$service}';" .
            "    const DATA_KEY = 'data_key';" .
            '}'
        );

        /** @var AbstractStandardConditionsStub $sut */
        $class = '\\test\\' . $service . '\\TestClass';
        $sut = new $class();
        /** @var IdentityInterface $actual */
        $actual = $sut->getQuery(['data_key' => 9999]);

        if ($expectClass === null) {
            static::assertNull($actual);
        } else {
            static::assertInstanceOf($expectClass, $actual);
            static::assertEquals(9999, $actual->getId());
        }
    }

    public function dpTestGetQuery()
    {
        return [
            [
                'SERVICE' => 'application',
                'expectClass' => DomainQry\Bookmark\ApplicationBundle::class,
            ],
            [
                'SERVICE' => 'licence',
                'expectClass' => DomainQry\Bookmark\LicenceBundle::class,
            ],
            [
                'SERVICE' => 'invalid',
                'expectClass' => null,
            ],
        ];
    }

    /**
     * @dataProvider dbTestRender
     */
    public function testRender($licType, $vehType, $expect)
    {
        /** @var m\MockInterface|AbstractStandardConditionsStub $sut */
        $sut = m::mock(AbstractStandardConditionsStub::class . '[getSnippet]');

        $sut->shouldReceive('getSnippet')
            ->once()
            ->with('unit_Prfx_' . $expect . '_LICENCE_CONDITIONS')
            ->andReturn('EXPECTED');

        $sut->setData(
            [
                'licenceType' => [
                    'id' => $licType,
                ],
                'vehicleType' => [
                    'id' => $vehType,
                ],
            ]
        );

        $sut->render();
    }

    public function dbTestRender()
    {
        return [
            [
                'licType' => Entity\Licence\Licence::LICENCE_TYPE_RESTRICTED,
                'vehType' => RefData::APP_VEHICLE_TYPE_HGV,
                'expect' => 'RESTRICTED',
            ],
            [
                'licType' => Entity\Licence\Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                'vehType' => RefData::APP_VEHICLE_TYPE_HGV,
                'expect' => 'STANDARD',
            ],
            [
                'licType' => Entity\Licence\Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                'vehType' => RefData::APP_VEHICLE_TYPE_HGV,
                'expect' => 'STANDARD_INT',
            ],
        ];
    }
}
