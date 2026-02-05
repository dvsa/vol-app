<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\VehicleRow;
use Dvsa\Olcs\Api\Service\Document\Parser\RtfParser;

/**
 * Vehicle Row test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VehicleRowTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new VehicleRow();
        $query = $bookmark->getQuery(['licence' => 7]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderWithNoData(): void
    {
        $parser = new RtfParser();
        $bookmark = new VehicleRow();
        $bookmark->setData([]);
        $bookmark->setParser($parser);

        $result = $bookmark->render();

        $this->assertEquals('', $result);
    }

    public function testRenderLicenceVehicles(): void
    {
        $data = [
            'licenceVehicles' => [
                [
                    'specifiedDate' => '2014-07-03',
                    'removalDate' => null,
                    'vehicle' => [
                        'platedWeight' => 12345,
                        'vrm' => 'VRM123'
                    ]
                ], [
                    'specifiedDate' => '2014-07-03',
                    'removalDate' => '2014-10-10',
                    'vehicle' => [
                        'platedWeight' => 12345,
                        'vrm' => 'VRM321'
                    ]
                ], [
                    'specifiedDate' => null,
                    'removalDate' => null,
                    'vehicle' => [
                        'platedWeight' => 23456,
                        'vrm' => 'VRM456'
                    ]
                ]
            ]
        ];

        $parser = $this->createPartialMock(\Dvsa\Olcs\Api\Service\Document\Parser\RtfParser::class, ['replace']);

        // note how we don't expect the second row to feature as it has been
        // marked as removed
        // similarly, the third row has no specified date so should also be omitted
        $expectedRowOne = [
            'SPEC_DATE' => '03-Jul-2014',
            'PLATED_WEIGHT' => 12345,
            'REG_MARK' => 'VRM123'
        ];

        $parser->expects($this->once())
            ->method('replace')
            ->with('snippet', $expectedRowOne)
            ->willReturn('foo');

        $bookmark = $this->createPartialMock(\Dvsa\Olcs\Api\Service\Document\Bookmark\VehicleRow::class, ['getSnippet']);

        $bookmark->expects($this->atLeastOnce())
            ->method('getSnippet')
            ->willReturn('snippet');

        $bookmark->setData($data);
        $bookmark->setParser($parser);

        $result = $bookmark->render();
        $this->assertEquals('foo', $result);
    }
}
