<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query as DomainQry;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Document\Bookmark\AbstractStandardConditions;
use Dvsa\Olcs\Transfer\FieldType\IdentityInterface;
use Dvsa\OlcsTest\Api\Service\Document\Bookmark\Stub\AbstractStandardConditionsStub;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\Document\Bookmark\AbstractStandardConditions::class)]
final class AbstractStandardConditionsTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetQuery')]
    public function testGetQuery(mixed $service, mixed $expectClass): void
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
            $this->assertNull($actual);
        } else {
            $this->assertInstanceOf($expectClass, $actual);
            $this->assertEquals(9999, $actual->getId());
        }
    }

    public static function dpTestGetQuery(): \Iterator
    {
        yield [
            'service' => 'application',
            'expectClass' => DomainQry\Bookmark\ApplicationBundle::class,
        ];
        yield [
            'service' => 'licence',
            'expectClass' => DomainQry\Bookmark\LicenceBundle::class,
        ];
        yield [
            'service' => 'invalid',
            'expectClass' => null,
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dbTestRender')]
    public function testRender(mixed $licType, mixed $vehType, mixed $expect): void
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

    public static function dbTestRender(): \Iterator
    {
        yield [
            'licType' => Entity\Licence\Licence::LICENCE_TYPE_RESTRICTED,
            'vehType' => RefData::APP_VEHICLE_TYPE_HGV,
            'expect' => 'RESTRICTED',
        ];
        yield [
            'licType' => Entity\Licence\Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            'vehType' => RefData::APP_VEHICLE_TYPE_HGV,
            'expect' => 'STANDARD',
        ];
        yield [
            'licType' => Entity\Licence\Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            'vehType' => RefData::APP_VEHICLE_TYPE_HGV,
            'expect' => 'STANDARD_INT',
        ];
    }
}
