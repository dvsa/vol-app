<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\BusReg;

use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\BusReg\GrantVarText3;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class GrantVarText3
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class GrantVarText3Test extends MockeryTestCase
{
    /**
     * @param string $variationReasons
     * @param string $text
     *
     * Test the Bus Reg GrantVarText3 filter
     */
    #[\PHPUnit\Framework\Attributes\Group('publicationFilter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('processTestProvider')]
    public function testProcess(mixed $variationReasons, mixed $text): void
    {
        $sut = new GrantVarText3();

        $startPoint = 'start point';
        $finishPoint = 'finish point';
        $busServices = 'bus services';
        $effectiveDate = '2014-05-14';
        $formattedEffectiveDate = '14 May 2014';

        $expectedString = sprintf(
            $text,
            $startPoint,
            $finishPoint,
            $busServices,
            $formattedEffectiveDate,
            $variationReasons
        );

        $input = [
            'busServices' => $busServices,
            'variationReasons' => $variationReasons
        ];

        $busRegMock = m::mock(BusRegEntity::class);
        $busRegMock->shouldReceive('getStartPoint')->andReturn($startPoint);
        $busRegMock->shouldReceive('getFinishPoint')->andReturn($finishPoint);
        $busRegMock->shouldReceive('getEffectiveDate')->andReturn($effectiveDate);
        $busRegMock->shouldReceive('getVariationReasons')->andReturn($variationReasons);

        $publicationLink = m::mock(PublicationLink::class)->makePartial();
        $publicationLink->shouldReceive('getBusReg')->andReturn($busRegMock);

        $output = $sut->process($publicationLink, new ImmutableArrayObject($input));
        $this->assertEquals($expectedString, $output->getText3());
    }

    /**
     * Data provider for processTest
     *
     * @return array
     */
    public static function processTestProvider(): array
    {
        return [
            ['var reasons', 'Operating between %s and %s given service number %s effective from %s. To amend %s.'],
            [null, 'Operating between %s and %s given service number %s effective from %s.']
        ];
    }
}
