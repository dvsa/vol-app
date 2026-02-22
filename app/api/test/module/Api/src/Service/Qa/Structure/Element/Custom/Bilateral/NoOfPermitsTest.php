<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\NoOfPermits;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsText;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsTest extends MockeryTestCase
{
    public function testGetRepresentation(): void
    {
        $noOfPermitsText1Representation = [
            'name' => 'name1',
            'label' => 'label1',
            'hint' => 'hint1',
            'value' => 'value1',
        ];

        $noOfPermitsText1 = m::mock(NoOfPermitsText::class);
        $noOfPermitsText1->shouldReceive('getRepresentation')
            ->withNoArgs()
            ->andReturn($noOfPermitsText1Representation);

        $noOfPermitsText2Representation = [
            'name' => 'name2',
            'label' => 'label2',
            'hint' => 'hint2',
            'value' => 'value2',
        ];

        $noOfPermitsText2 = m::mock(NoOfPermitsText::class);
        $noOfPermitsText2->shouldReceive('getRepresentation')
            ->withNoArgs()
            ->andReturn($noOfPermitsText2Representation);

        $noOfPermits = new NoOfPermits();
        $noOfPermits->addText($noOfPermitsText1);
        $noOfPermits->addText($noOfPermitsText2);

        $expectedRepresentation = [
            'texts' => [
                $noOfPermitsText1Representation,
                $noOfPermitsText2Representation,
            ]
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $noOfPermits->getRepresentation()
        );
    }
}
